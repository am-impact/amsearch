<?php
/**
 * Search / filtering for Craft.
 *
 * @package   Am Search
 * @author    Hubert Prein
 */
namespace Craft;

class AmSearchPlugin extends BasePlugin
{
    /**
     * @return null|string
     */
    public function getName()
    {
        if (craft()->plugins->getPlugin('amsearch')) {
            $pluginName = craft()->amSearch_settings->getSettingsByHandleAndType('pluginName', AmSearchModel::SettingGeneral);
            if ($pluginName && $pluginName->value) {
                return $pluginName->value;
            }
        }
        return Craft::t('a&m search');
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'a&m impact';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://www.am-impact.nl';
    }

    /**
     * Plugin has control panel section.
     *
     * @return boolean
     */
    public function hasCpSection()
    {
        return true;
    }

    /**
     * Plugin has Control Panel routes.
     *
     * @return array
     */
    public function registerCpRoutes()
    {
        return array(
            'amsearch/sets' => array(
                'action' => 'amSearch/sets/index'
            ),

            'amsearch/settings' => array(
                'action' => 'amSearch/settings/index'
            ),
        );
    }

    /**
     * Plugin has user permissions.
     *
     * @return array
     */
    public function registerUserPermissions()
    {
        return array(
            'accessAmSearchSettings' => array(
                'label' => Craft::t('Access to settings')
            )
        );
    }

    /**
     * Install essential information after installing the plugin.
     */
    public function onAfterInstall()
    {
        craft()->amSearch_install->install();
    }
}
