<?php
namespace Craft;

class m160512_081800_amSearch_requireLoginSetting extends BaseMigration
{
    public function safeUp()
    {
        $settings = array(
            array(
                'name' => 'Require login for results',
                'value' => false,
            ),
        );
        return craft()->amSearch_install->installSettings($settings, AmSearchModel::SettingGeneral);
    }
}
