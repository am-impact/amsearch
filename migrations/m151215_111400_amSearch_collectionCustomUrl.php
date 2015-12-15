<?php
namespace Craft;

class m151215_111400_amSearch_collectionCustomUrl extends BaseMigration
{
    public function safeUp()
    {
        $this->addColumnAfter('amsearch_collections', 'customUrl', array(ColumnType::Varchar), 'handle');
    }
}
