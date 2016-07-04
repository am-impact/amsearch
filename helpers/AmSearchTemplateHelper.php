<?php
namespace Craft;

/**
 * AmSearch - TemplateHelper
 */
class AmSearchTemplateHelper
{
    /**
     * Paginates an AmSearch getResults array.
     *
     * @param array $criteria
     *
     * @return array
     */
    public static function amSearchPaginate($criteria)
    {
        // Do we have the correct criteria?
        if (! is_array($criteria) || ! count($criteria) || count($criteria) < 1) {
            return null;
        }

        // Find the collections
        if (isset($criteria['collections'])) {
            $collections = $criteria['collections'];
        }
        else {
            $collections = $criteria[0];
        }

        // Find the parameters
        if (isset($criteria['params'])) {
            $params = $criteria['params'];
        }
        elseif (isset($criteria[2])) {
            $params = $criteria[2];
        }
        else {
            $params = array();
        }

        // Limit and offset
        $limit = null;
        if (isset($params['limit'])) {
            $limit = $params['limit'];
            unset($params['limit']); // Don't send the param to our service
        }
        $givenOffset = 0;
        if (isset($params['offset'])) {
            $givenOffset = $params['offset'];
            unset($params['offset']); // Don't send the param to our service
        }

        // Get the search results!
        $searchResults = craft()->amSearch_search->getResults($collections, $params);
        if (! $searchResults) {
            return null;
        }

        // Current page and total results
        $currentPage = craft()->request->getPageNum();
        $total = count($searchResults) - $givenOffset;

        // If they specified limit as null or 0 (for whatever reason), just assume it's all going to be on one page.
        if (! $limit) {
            $limit = $total;
        }

        $totalPages = ceil($total / $limit);

        $paginateVariable = new PaginateVariable();

        if ($totalPages == 0) {
            return array($paginateVariable, array());
        }

        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $offset = ($limit * ($currentPage - 1)) + $givenOffset;

        $last = $offset + $limit;

        if ($last > $total) {
            $last = $total;
        }

        $paginateVariable->first = $offset + 1;
        $paginateVariable->last = $last;
        $paginateVariable->total = $total;
        $paginateVariable->currentPage = $currentPage;
        $paginateVariable->totalPages = $totalPages;

        $searchResults = craft()->amSearch_search->filterResults($searchResults);

        return array($paginateVariable, $searchResults);
    }
}
