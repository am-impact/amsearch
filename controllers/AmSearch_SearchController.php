<?php
namespace Craft;

/**
 * AmSearch - Search controller
 */
class AmSearch_SearchController extends BaseController
{
    protected $allowAnonymous = true;

    /**
     * Start searching.
     */
    public function actionGetResults()
    {
        // Require login?
        $currentUser = craft()->userSession->getUser();
        $requireLogin = craft()->amSearch_settings->getSettingsByHandleAndType('requireLoginForResults', AmSearchModel::SettingGeneral);
        if ($requireLogin && $requireLogin->value && (! $currentUser || ! $currentUser->id)) {
            throw new HttpException(404);
        }

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
            // Filter before returning
            $results = craft()->amSearch_search->filterResults($results);

            $returnData['success'] = true;
            $returnData['results'] = $results;
        }

        $this->returnJson($returnData);
    }
}
