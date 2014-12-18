<?php
namespace Craft;

class AmSearchService extends BaseApplicationComponent
{
    public function getResults( $searchQuery, $sections, $limit, $offset )
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->section = $sections;
        $criteria->limit = $limit;
        $criteria->offset = $offset;
        $criteria->search = $searchQuery . '*';
        $criteria->order = 'score';

        return $criteria->find();
    }
}