<?php
namespace Craft;

/**
 * AmSearch - Elements service
 */
class AmSearch_ElementsService extends BaseApplicationComponent
{
    private $_allElementTypes = null;
    private $_ignoreElementTypes = array(
        ElementType::GlobalSet,
        ElementType::MatrixBlock,
        ElementType::Tag
    );

    public function __construct()
    {
        // Get available element types
        $this->_allElementTypes = craft()->elements->getAllElementTypes();
    }

    /**
     * Get available element types.
     *
     * @return array
     */
    public function getElementTypes()
    {
        $elementTypes = array();

        foreach ($this->_allElementTypes as $type => $elementType) {
            // Ignore some
            if (in_array($type, $this->_ignoreElementTypes)) {
                continue;
            }

            $elementTypes[$type] = $elementType->name;
        }

        return $elementTypes;
    }

    /**
     * Get available sources per element type.
     *
     * @return array
     */
    public function getElementTypeSources()
    {
        $sources = array();

        foreach ($this->_allElementTypes as $type => $elementType) {
            // Ignore some
            if (in_array($type, $this->_ignoreElementTypes)) {
                continue;
            }

            // Get the Element Type's sources
            $typeSources = $elementType->getSources();

            if ($typeSources) {
                foreach ($typeSources as $key => $source) {
                    if (! isset($source['heading'])) {
                        $sources[$type][] = array(
                            'label' => $source['label'],
                            'value' => $key,
                        );
                    }
                }
            }
        }

        return $sources;
    }

    /**
     * Get statuses per element type.
     *
     * @return array
     */
    public function getElementTypeStatuses()
    {
        $statuses = array();

        foreach ($this->_allElementTypes as $type => $elementType) {
            // Ignore some
            if (in_array($type, $this->_ignoreElementTypes)) {
                continue;
            }

            if ($elementType->hasStatuses()) {
                $statuses[$type] = $elementType->getStatuses();
            }
            else {
                $statuses[$type] = array(
                    '' => Craft::t('Any status')
                );
            }
        }

        return $statuses;
    }

    /**
     * Get available fields that can be used as important key.
     *
     * @return array
     */
    public function getFieldsForKey()
    {
        $fields = array();

        foreach ($this->_allElementTypes as $type => $elementType) {
            // Ignore some
            if (in_array($type, $this->_ignoreElementTypes)) {
                continue;
            }

            // Add empty option
            $fields[$type][] = array(
                'optgroup' => Craft::t('Standard')
            );
            $fields[$type][] = array(
                'label' => Craft::t('None'),
                'value' => '',
            );

            // Get element criteria
            $criteria = craft()->elements->getCriteria($type);

            // Add element title?
            if ($elementType->hasTitles()) {
                $fields[$type][] = array(
                    'label'    => Craft::t('Title'),
                    'value'    => 'title',
                );
            }

            // Add fields from optional fieldlayout?
            if ($elementType->hasContent()) {
                // Find content table
                $contentTable = $elementType->getContentTableForElementsQuery($criteria);
                if ($contentTable) {
                    // Find fields
                    $fieldColumns = $elementType->getFieldsForElementsQuery($criteria);

                    if ($fieldColumns) {
                        $fields[$type][] = array(
                            'optgroup' => Craft::t('Fields')
                        );
                        foreach ($fieldColumns as $field) {
                            // Add 'field_' otherwise we can't find the key when we get search results from MySQL
                            $fields[$type][] = array(
                                'label'    => $field->name,
                                'value'    => 'field_' . $field->handle,
                            );
                        }
                    }
                }
            }

            // Add search attributes?
            $attributes = $elementType->defineSearchableAttributes();
            if (count($attributes)) {
                $fields[$type][] = array(
                    'optgroup' => Craft::t('Attributes')
                );
                foreach ($attributes as $attribute) {
                    $fields[$type][] = array(
                        'label' => $attribute,
                        'value' => $attribute,
                    );
                }
            }
        }

        return $fields;
    }
}
