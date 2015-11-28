<?php
namespace Craft;

/**
 * AmSearch - Install service
 */
class AmSearch_InstallService extends BaseApplicationComponent
{
    /**
     * Install essential information.
     */
    public function install()
    {
        $this->_installGeneral();
        $this->_installSearch();
    }

    /**
     * Create a set of settings.
     *
     * @param array  $settings
     * @param string $settingType
     */
    public function installSettings(array $settings, $settingType)
    {
        // Make sure we have proper settings
        if (! is_array($settings)) {
            return false;
        }

        // Add settings
        foreach ($settings as $setting) {
            // Only install if we have proper keys
            if (! isset($setting['name']) || ! isset($setting['value'])) {
                continue;
            }

            // Add new setting!
            $settingRecord = new AmSearch_SettingRecord();
            $settingRecord->type = $settingType;
            $settingRecord->name = $setting['name'];
            $settingRecord->handle = $this->_camelCase($setting['name']);
            $settingRecord->value = $setting['value'];
            $settingRecord->save();
        }
        return true;
    }

    /**
     * Remove a set of settings.
     *
     * @param array  $settings
     * @param string $settingType
     * @return bool
     */
    public function removeSettings(array $settings, $settingType)
    {
        // Make sure we have proper settings
        if (! is_array($settings)) {
            return false;
        }

        // Remove settings
        foreach ($settings as $settingName) {
            $setting = craft()->amSearch_settings->getSettingsByHandleAndType($this->_camelCase($settingName), $settingType);
            if ($setting) {
                craft()->amSearch_settings->deleteSettingById($setting->id);
            }
        }
        return true;
    }

    /**
     * Install General settings.
     */
    private function _installGeneral()
    {
        $settings = craft()->config->get('general', 'amsearch');
        $this->installSettings($settings, AmSearchModel::SettingGeneral);
    }

    /**
     * Install Search settings.
     */
    private function _installSearch()
    {
        $settings = craft()->config->get('search', 'amsearch');
        $this->installSettings($settings, AmSearchModel::SettingSearch);
    }

    /**
     * Camel case a string.
     *
     * @param string $str
     *
     * @return string
     */
    private function _camelCase($str)
    {
        // Non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9]+/i', ' ', $str);

        // Camel case!
        return str_replace(' ', '', lcfirst(ucwords(strtolower(trim($str)))));
    }
}
