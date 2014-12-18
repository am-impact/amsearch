<?php
namespace Craft;

class AmSearchController extends BaseController
{
    public function actionGetResults()
    {
        $templatePath = craft()->request->getQuery('template', false);
        $sections = craft()->request->getQuery('sections', null);
        $limit = craft()->request->getQuery('limit', 10);
        $offset = craft()->request->getQuery('offset', 0);
        $searchQuery = craft()->request->getQuery('searchQuery', '');

        $results = array(
            'success' => false
        );

        if ($templatePath) {
            // Get search results
            $searchResults = craft()->amSearch->getResults( $searchQuery, $sections, $limit, $offset );

            // Render given template
            $variables = array( 'results' => $searchResults );
            $renderedHtml = $this->renderTemplate($templatePath, $variables, true);

            // Return results
            $results['success'] = true;
            $results['html'] = $renderedHtml;
        }

        $this->returnJson( $results );
    }
}