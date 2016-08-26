<?php
namespace Craft;

class AmSearchVariable
{
    /**
     * Get the Plugin's name.
     *
     * @example {{ craft.amSearch.name }}
     * @return string
     */
    public function getName()
    {
        $plugin = craft()->plugins->getPlugin('amsearch');
        return $plugin->getName();
    }

    /**
     * Get a setting value by their handle and type.
     *
     * @param string $handle
     * @param string $type
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function getSettingValue($handle, $type, $defaultValue = null)
    {
        return craft()->amSearch_settings->getSettingValue($handle, $type, $defaultValue);
    }


    // Collection methods
    // =========================================================================

    /**
     * Get collection by its ID.
     *
     * @param int $id
     *
     * @return AmSearch_CollectionModel|null
     */
    public function getCollectionById($id)
    {
        return craft()->amSearch_collections->getCollectionById($id);
    }

    /**
     * Get collection by its handle.
     *
     * @param string $handle
     *
     * @return AmSearch_CollectionModel|null
     */
    public function getCollectionByHandle($handle)
    {
        return craft()->amSearch_collections->getCollectionByHandle($handle);
    }

    /**
     * Get collections by their handle.
     *
     * @param array $handles
     *
     * @return array|null
     */
    public function getCollectionsByHandle($handles)
    {
        return craft()->amSearch_collections->getCollectionsByHandle($handles);
    }

    /**
     * Get all collections.
     *
     * @param string $indexBy      [Optional] Return the collections indexed by an attribute.
     * @param bool   $indexAllData [Optional] Whether to return all the data or just the navigation name.
     *
     * @return array
     */
    public function getAllCollections($indexBy = null, $indexAllData = false)
    {
        return craft()->amSearch_collections->getAllCollections($indexBy, $indexAllData);
    }


    // Search methods
    // =========================================================================

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
     * - order      Order by a certain key. (Note: Only available with one collection!)
     * - sort       Sort direction when the order param is given. (Note: Only available with one collection!)
     *
     * @return bool|array
     */
    public function getResults($collectionHandles, $params = array())
    {
        return craft()->amSearch_search->getResults($collectionHandles, $params);
    }
}
