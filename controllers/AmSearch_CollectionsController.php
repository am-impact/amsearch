<?php
namespace Craft;

/**
 * AmSearch - Collections controller
 */
class AmSearch_CollectionsController extends BaseController
{
    /**
     * Make sure the current has access.
     */
    public function __construct()
    {
        $user = craft()->userSession->getUser();
        if (! $user->can('accessAmSearchCollections')) {
            throw new HttpException(403, Craft::t('This action may only be performed by users with the proper permissions.'));
        }
    }

    /**
     * Show collections.
     */
    public function actionIndex()
    {
        $variables = array(
            'collections' => craft()->amSearch_collections->getAllCollections()
        );
        $this->renderTemplate('amSearch/collections/index', $variables);
    }

    /**
     * Create or edit a collection.
     *
     * @param array $variables
     */
    public function actionEditCollection(array $variables = array())
    {
        // Do we have a collection model?
        if (! isset($variables['collection'])) {
            // Get collection if available
            if (! empty($variables['collectionId'])) {
                $variables['collection'] = craft()->amSearch_collections->getCollectionById($variables['collectionId']);

                if (! $variables['collection']) {
                    throw new Exception(Craft::t('No collection exists with the ID “{id}”.', array('id' => $variables['collectionId'])));
                }
            }
            else {
                $variables['collection'] = new AmSearch_CollectionModel();
            }
        }

        // Get available collection types
        $variables['collectionTypes'] = array(
            AmSearchModel::CollectionNormal => Craft::t('Standard'),
            AmSearchModel::CollectionFuzzy  => Craft::t('Fuzzy'),
        );

        // Get available element types and filterable fields
        $variables['elementTypes'] = craft()->amSearch_elements->getElementTypes();
        $variables['elementTypeSources'] = craft()->amSearch_elements->getElementTypeSources();
        $variables['elementTypeStatuses'] = craft()->amSearch_elements->getElementTypeStatuses();
        $variables['fieldsForKey'] = craft()->amSearch_elements->getFieldsForKey();

        $this->renderTemplate('amsearch/collections/_edit', $variables);
    }

    /**
     * Save a collection.
     */
    public function actionSaveCollection()
    {
        $this->requirePostRequest();

        // Get collection if available
        $collectionId = craft()->request->getPost('collectionId');
        if ($collectionId) {
            $collection = craft()->amSearch_collections->getCollectionById($collectionId);

            if (! $collection) {
                throw new Exception(Craft::t('No collection exists with the ID “{id}”.', array('id' => $collectionId)));
            }
        }
        else {
            $collection = new AmSearch_CollectionModel();
        }

        // Collection attributes
        $collection->name        = craft()->request->getPost('name');
        $collection->handle      = craft()->request->getPost('handle');
        $collection->type        = craft()->request->getPost('type');
        $collection->elementType = craft()->request->getPost($collection->type . '_elementType');

        // Get settings
        $settings = craft()->request->getPost('settings');
        if (isset($settings[ $collection->type ][ $collection->elementType ])) {
            $collection->settings = $settings[ $collection->type ][ $collection->elementType ];
        }

        // Save collection
        if (craft()->amSearch_collections->saveCollection($collection)) {
            craft()->userSession->setNotice(Craft::t('Collection saved.'));

            $this->redirectToPostedUrl($collection);
        }
        else {
            craft()->userSession->setError(Craft::t('Couldn’t save collection.'));

            // Send the collection back to the template
            craft()->urlManager->setRouteVariables(array(
                'collection' => $collection
            ));
        }
    }

    /**
     * Test a collection.
     *
     * @param array $variables
     */
    public function actionTestCollection(array $variables = array())
    {
        // Do we have a collection model?
        if (! isset($variables['collection'])) {
            // Get collection if available
            if (! empty($variables['collectionId'])) {
                $collection = craft()->amSearch_collections->getCollectionById($variables['collectionId']);

                if (! $collection) {
                    throw new Exception(Craft::t('No collection exists with the ID “{id}”.', array('id' => $variables['collectionId'])));
                }
            }
            else {
                throw new Exception(Craft::t('No collection given.'));
            }
        }
        else {
            throw new Exception(Craft::t('No collection given.'));
        }

        // Set variables
        $variables['collection'] = $collection;
        // Get available collection types
        $variables['collectionType'] = ($collection->type == AmSearchModel::CollectionFuzzy ? Craft::t('Fuzzy') : Craft::t('Standard'));
        $variables['elementType'] = craft()->elements->getElementType($collection->elementType);
        $variables['source'] = $variables['elementType']->getSource($collection->settings['source']);

        // Load resources
        $js = sprintf('new Craft.AmSearch("%s", %s);',
            $collection->handle,
            $collection->type == AmSearchModel::CollectionFuzzy ? 'true' : 'false'
        );
        craft()->templates->includeJs($js);
        craft()->templates->includeJsResource('amsearch/js/AmSearch.js');
        if ($collection->type == AmSearchModel::CollectionFuzzy) {
            craft()->templates->includeJsResource('amsearch/js/fuzzy.min.js');
        }

        $this->renderTemplate('amsearch/collections/_test', $variables);
    }

    /**
     * Delete a collection.
     */
    public function actionDeleteCollection()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $id = craft()->request->getRequiredPost('id');

        $result = craft()->amSearch_collections->deleteCollectionById($id);
        $this->returnJson(array('success' => $result));
    }
}
