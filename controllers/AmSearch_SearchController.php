<?php
namespace Craft;

/**
 * AmSearch - Search controller
 */
class AmSearch_SearchController extends BaseController
{
    /**
     * Start searching.
     */
    public function actionGetResults()
    {
        // Get required information
        $collections = craft()->request->getRequiredParam('collections');
        $params = craft()->request->getParam('params', array());

        // Set return data
        $returnData = array(
            'success' => false
        );

        // Find search results!
        $results = craft()->amSearch_search->getResults($collections, $params);
        if ($results) {
            $returnData['success'] = true;
            $returnData['results'] = $results;
        }

        $this->returnJson($returnData);
    }
}
