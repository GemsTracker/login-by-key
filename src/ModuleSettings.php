<?php


namespace Gems\LoginByKey;

use Gems\Modules\ModuleSettingsAbstract;

class ModuleSettings extends ModuleSettingsAbstract
{
    public static $moduleName = 'LoginByKey';

    public static $eventSubscriber = ModuleSubscriber::class;
}
