<?php
namespace Craft;

/**
 * AmSearch - Settings controller
 */
class AmSearch_SettingsController extends BaseController
{
    /**
     * Make sure the current has access.
     */
    public function __construct()
    {
        $user = craft()->userSession->getUser();
        if (! $user->can('accessAmSearchSettings')) {
            throw new HttpException(403, Craft::t('This action may only be performed by users with the proper permissions.'));
        }
    }

    /**
     * Show General settings.
     */
    public function actionIndex()
    {
        $variables = array(
            'type'    => AmSearchModel::SettingGeneral,
            'general' => craft()->amSearch_settings->getAllSettingsByType(AmSearchModel::SettingGeneral)
        );
        $this->renderTemplate('amSearch/settings/index', $variables);
    }

    /**
     * Show General settings.
     */
    public function actionSearch()
    {
        $variables = array(
            'type'   => AmSearchModel::SettingSearch,
            'search' => craft()->amSearch_settings->getAllSettingsByType(AmSearchModel::SettingSearch)
        );
        $this->renderTemplate('amSearch/settings/search', $variables);
    }

    /**
     * Saves settings.
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        // Settings type
        $settingsType = craft()->request->getPost('settingsType', false);

        // Save settings!
        if ($settingsType) {
            $this->_saveSettings($settingsType);
        }
        else {
            craft()->userSession->setError(Craft::t('Couldn’t find settings type.'));
        }

        $this->redirectToPostedUrl();
    }

    /**
     * Save the settings for a specific type.
     *
     * @param string $type
     */
    private function _saveSettings($type)
    {
        $success = true;

        // Get all available settings for this type
        $availableSettings = craft()->amSearch_settings->getAllSettingsByType($type);

        // Save each available setting
        foreach ($availableSettings as $setting) {
            // Find new settings
            $newSettings = craft()->request->getPost($setting->handle, false);

            if ($newSettings !== false) {
                $setting->value = $newSettings;
                if(! craft()->amSearch_settings->saveSettings($setting)) {
                    $success = false;
                }
            }
        }

        // Save the settings in the plugins table
        $plugin = craft()->plugins->getPlugin('amSearch');
        craft()->plugins->savePluginSettings($plugin, $plugin->getSettings());

        if ($success) {
            craft()->userSession->setNotice(Craft::t('Settings saved.'));
        }
        else {
            craft()->userSession->setError(Craft::t('Couldn’t save settings.'));
        }
    }
}
