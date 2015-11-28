<?php
namespace Craft;

class AmSearch_CollectionRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'amsearch_collections';
    }

    protected function defineAttributes()
    {
        return array(
            'name'        => array(AttributeType::String, 'required' => true),
            'handle'      => array(AttributeType::String, 'required' => true),
            'type'        => array(AttributeType::String, 'required' => true),
            'elementType' => array(AttributeType::String, 'required' => true),
            'settings'    => array(AttributeType::Mixed),
        );
    }

    public function defineIndexes()
    {
        return array(
            array('columns' => array('type'), 'unique' => false),
            array('columns' => array('handle'), 'unique' => true),
        );
    }

    /**
     * Define validation rules
     *
     * @return array
     */
    public function rules()
    {
        return array(
            array(
                'name,handle',
                'required'
            ),
            array(
                'handle',
                'unique',
                'on' => 'insert'
            )
        );
    }

    /**
     * @return array
     */
    public function scopes()
    {
        return array(
            'ordered' => array(
                'order' => 'handle'
            )
        );
    }
}
