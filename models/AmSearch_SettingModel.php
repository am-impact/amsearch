<?php
namespace Craft;

class AmSearch_SettingModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'id'      => AttributeType::Number,
            'enabled' => AttributeType::Bool,
            'type'    => AttributeType::String,
            'name'    => AttributeType::String,
            'handle'  => AttributeType::String,
            'value'   => AttributeType::Mixed
        );
    }
}
