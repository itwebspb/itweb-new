<?php

// aspro.sku 1.1.1 updater

// module:
// /lib/components/list/base.php - update
// /views/default/properties_in_pict.php - add
// /views/default/properties_in_text.php - add
// /views/default/partials/prop_title.php - add
// /views/main/properties_in_pict.php - add
// /views/main/properties_in_text.php - add
// /views/main/partials/prop_title.php - add
// /views/partials/prop_title.php - delete
// /views/properties_in_pict.php - delete
// /views/properties_in_text.php - delete

// admin:
// /admin/group_rights.php - update
// /admin/options_tabs.php - update
// /admin/router.php - update
// /admin/settings.php - update
// /admin/update_module.php - update

// components:
// /sku.list.section/class.php - update
// /sku.list.section/templates/grouper/bitrix/catalog.section/grouper/template.php - update
// /sku.list/class.php - update
// /sku.list/templates/grouper/template.php - update

use Bitrix\Main\Application;
use Bitrix\Main\IO;
use Bitrix\Main\Loader;

require_once __DIR__.'/functions.php';

define('PARTNER_NAME', 'aspro');
define('MODULE_NAME', 'aspro.sku');
define('MODULE_NAME_SHORT', 'sku');
define('TEMPLATE_NAME', 'aspro_sku');
define('MODULE_PATH', '/bitrix/modules/'.MODULE_NAME);
define('COMPONENT_PATH', '/bitrix/components/'.PARTNER_NAME);
define('ADMIN_JS_PATH', '/bitrix/js/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT);
define('ADMIN_CSS_PATH', '/bitrix/css/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT);
define('ADMIN_IMAGES_PATH', '/bitrix/images/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT);
define('CURRENT_VERSION', GetCurVersion($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.MODULE_NAME.'/install/version.php'));
define('NEW_VERSION', GetCurVersion(__DIR__.'/install/version.php'));

UpdaterLog('START UPDATE '.CURRENT_VERSION.' -> '.NEW_VERSION.PHP_EOL);

// remove old bak files
RemoveOldBakFiles();

// remove some files and dirs
foreach ([
    MODULE_PATH.'/views/properties_in_text.php',
    MODULE_PATH.'/views/properties_in_pict.php',
    MODULE_PATH.'/views/partials/prop_title.php',

    COMPONENT_PATH.'/sku.list/templates/grouper/script.js',
    COMPONENT_PATH.'/sku.list/templates/grouper/script.min.js',
    COMPONENT_PATH.'/sku.list/templates/grouper/style.css',
    COMPONENT_PATH.'/sku.list/templates/grouper/style.min.css',
    COMPONENT_PATH.'/sku.list/templates/grouper/views/partials/prop_title.php',
    COMPONENT_PATH.'/sku.list/templates/grouper/views/properties_in_pict.php',
    COMPONENT_PATH.'/sku.list/templates/grouper/views/properties_in_text.php',
] as $fileDelete) {
    $fp = Application::getDocumentRoot().$fileDelete;
    if (IO\File::isFileExists($fp)) {
        CreateBakFile($fp);
        IO\File::deleteFile($fp);
    }
}

// create bak files
foreach ([
    MODULE_PATH.'/install/admin/group_rights.php',
    '/bitrix/admin/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.'/group_rights.php',

    MODULE_PATH.'/install/admin/options_tabs.php',
    '/bitrix/admin/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.'/options_tabs.php',

    MODULE_PATH.'/install/admin/router.php',
    '/bitrix/admin/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.'/router.php',

    MODULE_PATH.'/install/admin/settings.php',
    '/bitrix/admin/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.'/settings.php',

    MODULE_PATH.'/install/admin/update_module.php',
    '/bitrix/admin/'.PARTNER_NAME.'/'.MODULE_NAME_SHORT.'/update_module.php',

    MODULE_PATH.'/lib/components/list/base.php',

    COMPONENT_PATH.'/sku.list.section/class.php',
    COMPONENT_PATH.'/sku.list.section/templates/grouper/bitrix/catalog.section/grouper/template.php',
    COMPONENT_PATH.'/sku.list/class.php',
    COMPONENT_PATH.'/sku.list/templates/grouper/template.php',
] as $file) {
    CreateBakFile($_SERVER['DOCUMENT_ROOT'].$file);
}

// update admin section images
// CopyDirFiles(__DIR__.'/install/images', $_SERVER['DOCUMENT_ROOT'].ADMIN_IMAGES_PATH.'/', true, true);

// update admin page
CopyDirFiles(__DIR__.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.MODULE_NAME.'/', true, true);

// update admin js
// CopyDirFiles(__DIR__.'/install/js', $_SERVER['DOCUMENT_ROOT'].ADMIN_JS_PATH.'/', true, true);

// update admin css
// CopyDirFiles(__DIR__.'/install/css', $_SERVER['DOCUMENT_ROOT'].ADMIN_CSS_PATH.'/', true, true);

// update admin tools
// CopyDirFiles(__DIR__.'/install/tools', $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.MODULE_NAME.'/', true, true);

// update components
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.PARTNER_NAME.'/')) {
    CopyDirFiles(__DIR__.'/install/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/', true, true);
}

if (Loader::includeModule(MODULE_NAME)) {
    // if ($module = CModule::CreateModuleObject(MODULE_NAME)) {
    //     if (method_exists($module, 'createORMTables')) {
    //         $module->createORMTables();
    //     }
    // }

    // register new events
    // $eventManager = Bitrix\Main\EventManager::getInstance();

    // $arCompatibleEvents = [
    //     'search' => [
    //         ['onAsproGetAdditionalSearchTitleOptions', '\Aspro\sku\Events\SearchTitle', 'onAsproGetAdditionalSearchTitleOptionsHandler'],
    //     ],
    // ];

    // foreach ($arCompatibleEvents as $fromModuleId => $arModuleEvents) {
    //     foreach ($arModuleEvents as $arModuleEvent) {
    //         [$eventType, $toClass, $toMethod] = $arModuleEvent;
    //         $sort = $arModuleEvent[3] ?? null;
    //         $toPath = $arModuleEvent[4] ?? null;
    //         $toMethodArg = $arModuleEvent[5] ?? null;
    //         $eventManager->registerEventHandlerCompatible($fromModuleId, $eventType, MODULE_NAME, $toClass, $toMethod, $sort, $toPath, $toMethodArg);
    //     }
    // }
}

// current SITEs
// $arSites = GetSites();

// current IBLOCK_IDs
// $arIblocks = GetIBlocks();

// clear all sites cache in some components and dirs (include composite cache)
ClearAllSitesCacheDirs([
    'html_pages',
]);

// clear components cache
ClearAllSitesCacheComponents([
    'aspro:sku.list',
    'aspro:sku.list.section',
]);

UpdaterLog('FINISH UPDATE '.CURRENT_VERSION.' -> '.NEW_VERSION.PHP_EOL);
