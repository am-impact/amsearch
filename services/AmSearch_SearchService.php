<?php
namespace Craft;

/**
 * AmSearch - Search service
 */
class AmSearch_SearchService extends BaseApplicationComponent
{
    private $_siteUrl;
    private $_addTrailingSlash;

    private $_searchResults;
    private $_keywords;
    private $_scoreResults;

    private $_excerptPrefix = null;
    private $_excerptSuffix = null;
    private $_charsBeforeKeywords = null;
    private $_charsAfterKeywords = null;

    private $_handledElements;
    private $_collection;
    private $_searchParams;

    /**
     * Start searching.
     *
     * @param mixed  $collectionHandles
     * @param array  $params            [Optional] Set params.
     *
     * Available params:
     * - locale     Search for data from a certain locale.
     * - keywords   Search keywords.
     * - limit      Limit the search results.
     * - offset     Offset in the search results.
     *
     * @return bool|array
     */
    public function getResults($collectionHandles, $params = array())
    {
        // Get collections
        if (is_array($collectionHandles)) {
            $collections = craft()->amSearch_collections->getCollectionsByHandle($collectionHandles);
        }
        else {
            $collections = craft()->amSearch_collections->getCollectionByHandle($collectionHandles);
        }

        // Did we get any collections?
        if (! $collections) {
            return false;
        }
        elseif (! is_array($collections)) {
            $collections = array($collections);
        }

        // Set the important stuff
        $this->_siteUrl = UrlHelper::getSiteUrl('', null, null, $this->_getSearchParam('locale'));
        $this->_addTrailingSlash = craft()->config->get('addTrailingSlashesToUrls');
        $this->_searchResults = array();
        $this->_handledElements = array();
        $this->_searchParams = $params;

        // Get plugin search settings
        if (! $this->_excerptPrefix) {
            $searchSettings = craft()->amSearch_settings->getAllSettingsByType(AmSearchModel::SettingSearch);
            if ($searchSettings) {
                foreach ($searchSettings as $searchSetting) {
                    $this->{'_' . $searchSetting->handle} = $searchSetting->value;
                }
            }
            else {
                // Default
                $this->_excerptPrefix = '…';
                $this->_excerptSuffix = '…';
                $this->_charsBeforeKeywords = 100;
                $this->_charsAfterKeywords = 100;
            }
        }

        // Get data for each collection
        foreach ($collections as $collection) {
            // Set collection
            $this->_collection = $collection;

            // Get records!
            $this->_getRecordsForCollection();
        }

        // Limit and offset the results?
        $limit = $this->_getSearchParam('limit', false);
        if ($limit && is_numeric($limit)) {
            $offset = $this->_getSearchParam('offset', 0);
            $this->_searchResults = array_slice($this->_searchResults, $offset, $limit);
        }

        return $this->_searchResults;
    }

    /**
     * Get search parameter value.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    private function _getSearchParam($name, $default = null)
    {
        return isset($this->_searchParams[$name]) ? $this->_searchParams[$name] : $default;
    }

    /**
     * Get a collection setting value.
     *
     * @param string $name
     *
     * @return mixed
     */
    private function _getCollectionSetting($name)
    {
        if ($this->_collection->settings) {
            if (isset($this->_collection->settings[$name])) {
                return $this->_collection->settings[$name];
            }
        }

        return null;
    }

    /**
     * Get database records for current collection.
     */
    private function _getRecordsForCollection()
    {
        // Get element criteria
        $criteria = craft()->elements->getCriteria($this->_collection->elementType);
        $criteria->locale = $this->_getSearchParam('locale');

        // Get element type
        $elementType = $criteria->getElementType();

        // Set element source
        if ($this->_getCollectionSetting('source')) {
            $source = $elementType->getSource($this->_getCollectionSetting('source'));

            // Does the source specify any criteria attributes?
            if ($source && ! empty($source['criteria'])) {
                $criteria->setAttributes($source['criteria']);
            }
        }

        // Set element status
        if ($this->_getCollectionSetting('status')) {
            $criteria->status = $this->_getCollectionSetting('status');
        }

        // Get the element's query
        $query = craft()->elements->buildElementsQuery($criteria);
        if (! $query) {
            return false;
        }

        // Set search criteria?
        $this->_keywords = null; // Always reset first, regardless of param
        $this->_scoreResults = null; // Always reset first, regardless of param
        if ($this->_getSearchParam('keywords') && trim($this->_getSearchParam('keywords')) != '') {
            $this->_keywords = StringHelper::normalizeKeywords($this->_getSearchParam('keywords'));
            if (! $this->_setSearchCriteria($criteria, $query)) {
                return false; // No search results!
            }
        }

        // Find records!
        $elements = $query->queryAll();
        if ($elements) {
            $this->_handleElements($elements);
        }
    }

    /**
     * Handle records / elements from a collection.
     *
     * @param array $elements
     */
    private function _handleElements($elements)
    {
        foreach ($elements as $element) {
            // Did we add this element to the search results already?
            if (isset($this->_handledElements[ $element['id'] ])) {
                continue;
            }

            // Handle element
            switch ($this->_collection->type) {
                case 'fuzzy':
                    $searchResult = $this->_handleFuzzyElement($element);
                    break;

                default:
                    $searchResult = $this->_handleNormalElement($element);
                    break;
            }

            // Do we have a valid search result?
            if ($searchResult !== false) {
                // Add collection data for this element
                $searchResult['collection'] = array(
                    'name'   => $this->_collection->name,
                    'handle' => $this->_collection->handle,
                );

                // Add this element to the search results
                $this->_searchResults[] = $searchResult;
            }

            // We handled the element!
            $this->_handledElements[ $element['id'] ] = true;
        }

        // Sort search results
        switch ($this->_collection->type) {
            case 'fuzzy':
                $this->_sortSearchResults('fuzzy');
                break;

            default:
                $this->_sortSearchResults();
                break;
        }
    }

    /**
     * Handle a fuzzy collection element.
     *
     * @param array $element
     *
     * @return mixed
     */
    private function _handleFuzzyElement($element)
    {
        // What is our fuzzy key that'll display the results?
        $fuzzyKey = $this->_getCollectionSetting('fuzzyKey');

        // Does our element have the fuzzy key?
        if (empty($fuzzyKey) || ! isset($element[$fuzzyKey])) {
            return false;
        }

        // Set search result
        $searchResult = array(
            'fuzzy' => $element[$fuzzyKey],
            'type'  => Craft::t($element['type']),
            'url'   => $this->_getElementUrl($element),
        );

        return $searchResult;
    }

    /**
     * Handle a normal collection element.
     *
     * @param array $element
     *
     * @return mixed
     */
    private function _handleNormalElement($element)
    {
        // Set search result
        $searchResult = array(
            'excerpt' => $this->_getElementExcerpt($element),
            'type'    => Craft::t($element['type']),
            'url'     => $this->_getElementUrl($element),
        );

        // Should we set a search score on the element?
        if (isset($this->_scoreResults[ $element['id'] ])) {
            $searchResult['searchScore'] = $this->_scoreResults[ $element['id'] ];
        }

        // Correct fields to field handles
        foreach ($element as $key => $value) {
            $key = str_replace('field_', '', $key);
            $searchResult[$key] = $value;
        }

        return $searchResult;
    }

    /**
     * Get an element's excerpt.
     *
     * @param array $element
     *
     * @return string
     */
    private function _getElementExcerpt($element)
    {
        // What is our excerpt key?
        $excerptKey = $this->_getCollectionSetting('excerptKey');

        // Do we have an excerpt option?
        if (empty($excerptKey) || ! isset($element[$excerptKey])) {
            return '';
        }

        // Get our full string
        $fullString = $element[$excerptKey];

        // Strip HTML from string
        $fullString = StringHelper::stripHtml($fullString);

        // Do we even have keywords?
        if (! $this->_keywords) {
            return $fullString;
        }

        // Excerpt settings
        $prefix = $this->_excerptPrefix;
        $suffix = $this->_excerptSuffix;

        // Where are the keywords located?
        $keywordsPosition = stripos($fullString, $this->_keywords);

        // Find start
        $extractStart = $keywordsPosition - $this->_charsBeforeKeywords;
        if ($extractStart < 0) {
            $extractStart = 0;
            $prefix = '';
        }

        // Find end
        $extractEnd = $keywordsPosition + strlen($this->_keywords) + $this->_charsAfterKeywords;
        if ($extractEnd > strlen($fullString)) {
            $extractEnd = strlen($fullString);
            $suffix = '';
        }

        // Get excerpt!
        $plainText = substr($fullString, $extractStart, $extractEnd - $extractStart);
        $plainText = preg_replace("/(" . $this->_keywords . ")/i", "<strong>$1</strong>", StringHelper::convertToUTF8($plainText));
        return new \Twig_Markup($prefix . $plainText . $suffix, craft()->templates->getTwig()->getCharset());
    }

    /**
     * Get an element's URL.
     *
     * @param array $element
     *
     * @return string
     */
    private function _getElementUrl($element)
    {
        $url = '';

        // Element type URL?
        if (isset($element['uri']) && ! empty($element['uri'])) {
            $url = $this->_siteUrl
                 . ($element['uri'] != '__home__' ? $element['uri'] : '')
                 . ($this->_addTrailingSlash ? '/' : '');
        }

        // Custom URL?
        if ($this->_collection->customUrl) {
            // Translate the URL first
            $url = Craft::t($this->_collection->customUrl);

            // Parse through object
            $url = craft()->templates->renderObjectTemplate($url, $element);

            // Parse through environment variables
            $url = craft()->config->parseEnvironmentString($url);
        }

        return $url;
    }

    /**
     * Set search criteria.
     *
     * @param ElementCriteriaModel $criteria
     * @param DbCommand            &$query
     */
    private function _setSearchCriteria($criteria, DbCommand &$query)
    {
        $elementIds = $this->_getElementIdsFromQuery($query);
        $scoreResults = craft()->search->filterElementIdsByQuery($elementIds, $this->_getSearchParam('keywords'), true, $criteria->locale, true);

        // No results?
        if (! $scoreResults) {
            return false;
        }

        $filteredElementIds = array_keys($scoreResults);

        $query->andWhere(array('in', 'elements.id', $filteredElementIds));

        $this->_scoreResults = $scoreResults;

        return true;
    }

    /**
     * Returns the unique element IDs that match a given element query.
     *
     * @param DbCommand $query
     *
     * @return array
     */
    private function _getElementIdsFromQuery(DbCommand $query)
    {
        // Get the matched element IDs, and then have the SearchService filter them.
        $elementIdsQuery = craft()->db->createCommand()
            ->select('elements.id')
            ->from('elements elements');

        $elementIdsQuery->setWhere($query->getWhere());
        $elementIdsQuery->setJoin($query->getJoin());

        $elementIdsQuery->params = $query->params;
        return $elementIdsQuery->queryColumn();
    }

    /**
     * Sort search results.
     *
     * @param string $key
     */
    private function _sortSearchResults($key = 'searchScore')
    {
        usort($this->_searchResults, function($a, $b) use ($key) {
            if ($key == 'searchScore') {
                return (isset($a[$key]) ? $a[$key] : 0) < (isset($b[$key]) ? $b[$key] : 0);
            }
            else {
                return strcmp((isset($a[$key]) ? $a[$key] : ''), (isset($b[$key]) ? $b[$key] : ''));
            }
        });
    }
}
