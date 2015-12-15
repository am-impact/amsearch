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
     * @return string|null
     */
    public function getSchemaVersion()
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
     * @return string
     */
    public function getSettingsUrl()
    {
        return 'amsearch/settings';
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
            'amsearch/collections' => array(
                'action' => 'amSearch/collections/index'
            ),
            'amsearch/collections/new' => array(
                'action' => 'amSearch/collections/editCollection'
            ),
            'amsearch/collections/edit/(?P<collectionId>\d+)' => array(
                'action' => 'amSearch/collections/editCollection'
            ),
            'amsearch/collections/test/(?P<collectionId>\d+)' => array(
                'action' => 'amSearch/collections/testCollection'
            ),

            'amsearch/settings' => array(
                'action' => 'amSearch/settings/index'
            ),
            'amsearch/settings/search' => array(
                'action' => 'amSearch/settings/search'
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
            'accessAmSearchCollections' => array(
                'label' => Craft::t('Access to collections')
            ),
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

    /**
     * Add Twig Extensions.
     */
    public function addTwigExtension()
    {
        Craft::import('plugins.amsearch.helpers.AmSearchTemplateHelper');
        Craft::import('plugins.amsearch.twigextensions.AmSearchPaginate_Node');
        Craft::import('plugins.amsearch.twigextensions.AmSearchPaginate_TokenParser');
        Craft::import('plugins.amsearch.twigextensions.SearchTwigExtension');

        return new SearchTwigExtension();
    }
}
