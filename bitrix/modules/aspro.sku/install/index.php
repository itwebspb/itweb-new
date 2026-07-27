<?php
/**
 * Aspro:Sku module.
 *
 * @copyright 2025 Aspro
 */

use Aspro\Sku;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class aspro_sku extends CModule
{
    public const partnerName = 'aspro';
    public const solutionName = 'sku';
    public const moduleId = 'aspro.sku';

    public $MODULE_ID = 'aspro.sku';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];

        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include $path.'/version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('ASPRO_SKU_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('ASPRO_SKU_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('ASPRO_SKU_PARTNER');
        $this->PARTNER_URI = Loc::getMessage('ASPRO_SKU_PARTNER_URI');
    }

    public function InstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $connection = Application::getConnection();

        RegisterModule($this->MODULE_ID);

        Loader::includeModule($this->MODULE_ID);

        $this->createORMTables();

        return true;
    }

    public function createORMTables()
    {
        $connection = Application::getConnection();
        foreach ($this->getORMTables() as $table) {
            if (!$connection->isTableExists($table::getTableName())) {
                $table::getEntity()->createDbTable();
            }
        }
    }

    public function getORMTables()
    {
        $arTables = [
            '\Aspro\Sku\Orm\RulesSkuTable',
        ];

        return $arTables;
    }

    public function UnInstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $connection = Application::getConnection();

        Loader::includeModule($this->MODULE_ID);

        $this->dropORMTables();

        COption::RemoveOption($this->MODULE_ID);
        UnRegisterModule($this->MODULE_ID);

        return true;
    }

    public function dropORMTables()
    {
        $connection = Application::getConnection();
        foreach ($this->getORMTables() as $table) {
            if ($connection->isTableExists($table::getTableName())) {
                $connection->dropTable($table::getTableName());

                $table::getEntity()->cleanCache();
            }
        }
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__.'/admin/', Application::getDocumentRoot().BX_ROOT.'/admin/'.static::partnerName.'/'.static::solutionName, true, true);
        CopyDirFiles(__DIR__.'/components/', Application::getDocumentRoot().BX_ROOT.'/components', true, true);

        CopyDirFiles(__DIR__.'/css/', Application::getDocumentRoot().BX_ROOT.'/css/'.static::partnerName.'/'.static::solutionName, true, true);
        CopyDirFiles(__DIR__.'/js/', Application::getDocumentRoot().BX_ROOT.'/js/'.static::partnerName.'/'.static::solutionName, true, true);
        CopyDirFiles(__DIR__.'/images/', Application::getDocumentRoot().BX_ROOT.'/images/'.static::partnerName.'/'.static::solutionName, true, true);

        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFilesEx(BX_ROOT.'/admin/'.static::partnerName.'/'.static::solutionName.'/');
        DeleteDirFilesEx(BX_ROOT.'/css/'.static::partnerName.'/'.static::solutionName.'/');
        DeleteDirFilesEx(BX_ROOT.'/js/'.static::partnerName.'/'.static::solutionName.'/');
        DeleteDirFilesEx(BX_ROOT.'/images/'.static::partnerName.'/'.static::solutionName.'/');

        $this->UnInstallComponents();

        return true;
    }

    public function UnInstallComponents()
    {
        DeleteDirFilesEx(BX_ROOT.'/components/'.static::partnerName.'/sku.list/');
        return true;
    }

    public function DoInstall()
    {
        Loader::includeModule($this->MODULE_ID);

        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallEvents();
    }

    public function DoUninstall()
    {
        Loader::includeModule($this->MODULE_ID);

        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->UnInstallEvents();
    }

    public function getEvents()
    {
        $arEvents = [];

        return $arEvents;
    }

    public function getCompatibleEvents()
    {
        $arCompatibleEvents = [
            'main' => [
                ['OnEndBufferContent', '\Aspro\Sku\Events\Page', 'onEndBufferContentHandler'],
            ],
            'iblock' => [
                ['OnAfterIBlockElementAdd', '\Aspro\Sku\Events\Element', 'onAfterIBlockElementAddHandler'],
                ['OnAfterIBlockElementUpdate', '\Aspro\Sku\Events\Element', 'onAfterIblockElementUpdateHandler'],
                ['OnAfterIBlockElementDelete', '\Aspro\Sku\Events\Element', 'onAfterIBlockElementDeleteHandler'],
            ]
        ];

        return $arCompatibleEvents;
    }

    public function InstallEvents()
    {
        $eventManager = Bitrix\Main\EventManager::getInstance();

        $arCompatibleEvents = $this->getCompatibleEvents();
        foreach ($arCompatibleEvents as $fromModuleId => $arModuleEvents) {
            foreach ($arModuleEvents as $arModuleEvent) {
                [$eventType, $toClass, $toMethod] = $arModuleEvent;
                $sort = $arModuleEvent[3] ?? null;
                $toPath = $arModuleEvent[4] ?? null;
                $toMethodArg = $arModuleEvent[5] ?? null;
                $eventManager->registerEventHandlerCompatible($fromModuleId, $eventType, $this->MODULE_ID, $toClass, $toMethod, $sort, $toPath, $toMethodArg);
            }
        }

        $arEvents = $this->getEvents();
        foreach ($arEvents as $fromModuleId => $arModuleEvents) {
            foreach ($arModuleEvents as $arModuleEvent) {
                [$eventType, $toClass, $toMethod] = $arModuleEvent;
                $sort = $arModuleEvent[3] ?? null;
                $toPath = $arModuleEvent[4] ?? null;
                $toMethodArg = $arModuleEvent[5] ?? null;
                $eventManager->registerEventHandler($fromModuleId, $eventType, $this->MODULE_ID, $toClass, $toMethod, $sort, $toPath, $toMethodArg);
            }
        }

        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = Bitrix\Main\EventManager::getInstance();

        $arEvents = $this->getCompatibleEvents() + $this->getEvents();
        foreach ($arEvents as $fromModuleId => $arModuleEvents) {
            foreach ($arModuleEvents as $arModuleEvent) {
                [$eventType, $toClass, $toMethod] = $arModuleEvent;
                $toPath = $arModuleEvent[4] ?? null;
                $toMethodArg = $arModuleEvent[5] ?? null;
                $eventManager->unRegisterEventHandler($fromModuleId, $eventType, $this->MODULE_ID, $toClass, $toMethod, $toPath, $toMethodArg);
            }
        }

        return true;
    }
}
