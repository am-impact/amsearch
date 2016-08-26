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
     * Redirect index.
     */
    public function actionIndex()
    {
        $this->redirect('amsearch/settings/general');
    }

    /**
     * Show settings.
     *
     * @param array $variables
     */
    public function actionShowSettings(array $variables = array())
    {
        // Do we have a settings type?
        if (! isset($variables['settingsType'])) {
            throw new Exception(Craft::t('Settings type is not set.'));
        }
        $settingsType = $variables['settingsType'];

        // Do we have any settings?
        $settings = craft()->amSearch_settings->getSettingsByType($settingsType);
        if (! $settings) {
            throw new Exception(Craft::t('There are no settings available for settings type “{type}”.', array('type' => $settingsType)));
        }

        // Show settings!
        $variables['type'] = $settingsType;
        $variables[$settingsType] = $settings;
        $this->renderTemplate('amSearch/settings/' . $settingsType, $variables);
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
        $availableSettings = craft()->amSearch_settings->getSettingsByType($type);

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

        if ($success) {
            craft()->userSession->setNotice(Craft::t('Settings saved.'));
        }
        else {
            craft()->userSession->setError(Craft::t('Couldn’t save settings.'));
        }
    }
}
