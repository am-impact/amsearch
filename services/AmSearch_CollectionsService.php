<?php
namespace Craft;

/**
 * AmSearch - Collections service
 */
class AmSearch_CollectionsService extends BaseApplicationComponent
{
    /**
     * Get collection by its ID.
     *
     * @param int $id
     *
     * @return AmSearch_CollectionModel|null
     */
    public function getCollectionById($id)
    {
        $collectionRecord = AmSearch_CollectionRecord::model()->findById($id);
        if ($collectionRecord) {
            return AmSearch_CollectionModel::populateModel($collectionRecord);
        }
        return null;
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
        $collectionRecord = AmSearch_CollectionRecord::model()->findByAttributes(array('handle' => $handle));
        if ($collectionRecord) {
            return AmSearch_CollectionModel::populateModel($collectionRecord);
        }
        return null;
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
        $collectionRecords = AmSearch_CollectionRecord::model()->findAllByAttributes(array('handle' => $handles));
        if ($collectionRecords) {
            return AmSearch_CollectionModel::populateModels($collectionRecords);
        }
        return null;
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
        $collectionRecords = AmSearch_CollectionRecord::model()->ordered()->findAll();
        $collections = AmSearch_CollectionModel::populateModels($collectionRecords);
        if ($indexBy !== null) {
            $indexedCollections = array();
            foreach ($collections as $collection) {
                $indexedCollections[$collection->$indexBy] = $indexAllData ? $collection : $collection->name;
            }
            return $indexedCollections;
        }
        return $collections;
    }

    /**
     * Save a collection.
     *
     * @param AmSearch_CollectionModel $collection
     *
     * @throws Exception
     * @return bool
     */
    public function saveCollection(AmSearch_CollectionModel $collection)
    {
        // Get the collection record
        if ($collection->id) {
            $collectionRecord = AmSearch_CollectionRecord::model()->findById($collection->id);

            if (! $collectionRecord) {
                throw new Exception(Craft::t('No collection exists with the ID “{id}”.', array('id' => $collection->id)));
            }
        }
        else {
            $collectionRecord = new AmSearch_CollectionRecord();
        }

        // Collection attributes
        $collectionRecord->setAttributes($collection->getAttributes(), false);

        // Validate the attributes
        $collectionRecord->validate();
        $collection->addErrors($collectionRecord->getErrors());

        if (! $collection->hasErrors()) {
            // Save the collection!
            return $collectionRecord->save(false); // Skip validation now
        }

        return false;
    }

    /**
     * Delete an collection.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteCollectionById($id)
    {
        return craft()->db->createCommand()->delete('amsearch_collections', array('id' => $id));
    }
}
