<?php
namespace Craft;

class AmSearch_CollectionModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'id'          => AttributeType::Number,
            'name'        => AttributeType::String,
            'handle'      => AttributeType::String,
            'type'        => AttributeType::String,
            'elementType' => AttributeType::String,
            'settings'    => AttributeType::Mixed,
        );
    }
}
