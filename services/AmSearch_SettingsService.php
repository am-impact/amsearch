<?php
namespace Craft;

/**
 * AmSearch - Settings service
 */
class AmSearch_SettingsService extends BaseApplicationComponent
{
    /**
     * Get all settings.
     *
     * @return array
     */
    public function getSettings()
    {
        $settingRecords = AmSearch_SettingRecord::model()->ordered()->findAll();
        return AmSearch_SettingModel::populateModels($settingRecords, 'handle');
    }

    /**
     * Get all settings by their type.
     *
     * @param string $type
     * @param bool   $enabled [Optional] Whether to include the enabled as search attribute.
     *
     * @return AmSearch_SettingModel
     */
    public function getSettingsByType($type, $enabled = '*')
    {
        $attributes = array(
            'type' => $type
        );

        // Include enabled attribute?
        if ($enabled !== '*') {
            $attributes['enabled'] = $enabled;
        }

        // Find records
        $settingRecords = AmSearch_SettingRecord::model()->ordered()->findAllByAttributes($attributes);
        if ($settingRecords) {
            return AmSearch_SettingModel::populateModels($settingRecords, 'handle');
        }
        return null;
    }

    /**
     * Get a setting by their handle and type.
     *
     * @param string $handle
     * @param string $type
     *
     * @return AmSearch_SettingModel
     */
    public function getSettingByHandleAndType($handle, $type)
    {
        $attributes = array(
            'type' => $type,
            'handle' => $handle
        );

        // Find record
        $settingRecord = AmSearch_SettingRecord::model()->findByAttributes($attributes);
        if ($settingRecord) {
            return AmSearch_SettingModel::populateModel($settingRecord);
        }
        return null;
    }

    /**
     * Get a setting value by their handle and type.
     *
     * @param string $handle
     * @param string $type
     * @param mixed  $defaultValue
     *
     * @return AmSearch_SettingModel
     */
    public function getSettingValue($handle, $type, $defaultValue = null)
    {
        $attributes = array(
            'type' => $type,
            'handle' => $handle
        );

        // Find record
        $settingRecord = AmSearch_SettingRecord::model()->findByAttributes($attributes);
        if ($settingRecord) {
            return $settingRecord->value;
        }
        return $defaultValue;
    }

    /**
     * Check whether a setting value is enabled.
     * Note: Only for (booleans) light switches.
     *
     * @return bool
     */
    public function isSettingValueEnabled($handle, $type)
    {
        $setting = $this->getSettingByHandleAndType($handle, $type);
        if (is_null($setting)) {
            return false;
        }
        return $setting->value;
    }

    /**
     * Save settings.
     *
     * @param AmSearch_SettingModel
     *
     * @return bool
     */
    public function saveSettings(AmSearch_SettingModel $settings)
    {
        if (! $settings->id) {
            return false;
        }

        $settingsRecord = AmSearch_SettingRecord::model()->findById($settings->id);

        if (! $settingsRecord) {
            throw new Exception(Craft::t('No settings exists with the ID “{id}”.', array('id' => $settings->id)));
        }

        // Set attributes
        $properSettings = $settings->value;
        if (is_array($properSettings)) {
            $properSettings = json_encode($settings->value);
        }
        $settingsRecord->setAttributes($settings->getAttributes(), false);
        $settingsRecord->setAttribute('value', $properSettings);

        // Validate
        $settingsRecord->validate();
        $settings->addErrors($settingsRecord->getErrors());

        // Save settings
        if (! $settings->hasErrors()) {
            // Save in database
            return $settingsRecord->save();
        }
        return false;
    }

    /**
     * Delete a setting.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteSettingById($id)
    {
        return craft()->db->createCommand()->delete('amsearch_settings', array('id' => $id));
    }
}
