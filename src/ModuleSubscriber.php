<?php


namespace Gems\LoginByKey;

use Gems\Event\Application\GetDatabasePaths;
use Gems\Event\Application\MenuAdd;
use Gems\Event\Application\SetFrontControllerDirectory;
use Gems\Event\Application\ZendTranslateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModuleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            GetDatabasePaths::NAME => [
                ['getDatabasePaths'],
            ],
            MenuAdd::NAME => [
                ['addToMenu']
            ],
            SetFrontControllerDirectory::NAME => [
                ['setFrontControllerDirectory'],
            ],
            ZendTranslateEvent::NAME => [
                ['addTranslation'],
            ],
        ];
    }

    public function addToMenu(MenuAdd $event)
    {
        $menu = $event->getMenu();
        $translateAdapter = $event->getTranslatorAdapter();

        $indexPage = $menu->findController('index', 'login');
        $indexPage->addPage($translateAdapter->_('Login by key'), 'pr.nologin', 'login-by-key', 'index');
    }

    /**
     * @param \Gems\Event\Application\ZendTranslateEvent $event
     * @throws \Zend_Translate_Exception
     */
    public function addTranslation(ZendTranslateEvent $event)
    {
        $event->addTranslationByDirectory(ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'languages');
    }

    public function getDatabasePaths(GetDatabasePaths $event)
    {
        $path = ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'db';
        $event->addPath(ModuleSettings::$moduleName, $path);
    }

    public function setFrontControllerDirectory(SetFrontControllerDirectory $event)
    {
        $applicationPath = ModuleSettings::getVendorPath() . DIRECTORY_SEPARATOR . 'controllers';
        $event->setControllerDirIfControllerExists($applicationPath);
    }
}
