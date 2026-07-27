<?php

// aspro.popup 1.0.4 updater
// changed files

// module:
// /admin/update_module.php - update
// /lib/property/conditiontype.php - update

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;

require_once __DIR__.'/functions.php';

define('PARTNER_NAME', 'aspro');
define('MODULE_NAME', 'aspro.popup');
define('MODULE_NAME_SHORT', 'popup');
define('TEMPLATE_NAME', 'aspro_popup');
define('MODULE_PATH', '/bitrix/modules/'.MODULE_NAME);
define('COMPONENT_PATH', '/bitrix/components/'.PARTNER_NAME);
define('ADMIN_JS_PATH', '/bitrix/js/'.MODULE_NAME);
define('ADMIN_CSS_PATH', '/bitrix/css/'.MODULE_NAME);
define('CURRENT_VERSION', GetCurVersion($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.MODULE_NAME.'/install/version.php'));
define('NEW_VERSION', GetCurVersion(__DIR__.'/install/version.php'));

UpdaterLog('START UPDATE '.CURRENT_VERSION.' -> '.NEW_VERSION.PHP_EOL);

// remove old bak files
RemoveOldBakFiles();

// create bak files
foreach ([
    MODULE_PATH.'/admin/update_module.php',
    MODULE_PATH.'/lib/property/conditiontype.php',
] as $file) {
    CreateBakFile($_SERVER['DOCUMENT_ROOT'].$file);
}

if (Loader::includeModule(MODULE_NAME)) {
    // update admin section images
    // CopyDirFiles(__DIR__.'/install/images', $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/'.MODULE_NAME.'/', true, true);

    // update admin page
    // CopyDirFiles(__DIR__.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.MODULE_NAME.'/', true, true);

    // update admin js
    // CopyDirFiles(__DIR__.'/install/js', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.MODULE_NAME.'/', true, true);

    // update admin css
    // CopyDirFiles(__DIR__.'/install/css', $_SERVER['DOCUMENT_ROOT'].'/bitrix/css/'.MODULE_NAME.'/', true, true);

    // update admin tools
    // CopyDirFiles(__DIR__.'/install/tools', $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.MODULE_NAME.'/', true, true);

    // update components
    // if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.PARTNER_NAME.'/')) {
    //     CopyDirFiles(__DIR__.'/install/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/', true, true);
    // }

    // current SITEs
    $arSites = GetSites();

    // current IBLOCK_IDs
    $arIblocks = GetIBlocks();

    $bSolutionSiteExists = false;
    if ($arSites && $arIblocks) {	
        foreach ($arSites as $siteId => $arSite) {
            $arSite['DIR'] = str_replace('//', '/', '/'.$arSite['DIR']);
            if (!strlen($arSite['DOC_ROOT'])) {
                $arSite['DOC_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
            }
            
            $arSite['DOC_ROOT'] = str_replace('//', '/', $arSite['DOC_ROOT'].'/');
            $siteDir = str_replace('//', '/', $arSite['DOC_ROOT'].$arSite['DIR']);
        }
    }

    // is composite enabled
    // $compositeMode = IsCompositeEnabled();

    // clear all sites cache in some components and dirs (include composite cache)
    // ClearAllSitesCacheDirs([
    //     'html_pages',
    //     'cache/js',
    //     'cache/css'
    // ]);

    ClearAllSitesCacheComponents([
        // 'aspro:marketing.popup',
    ]);

    // if ($compositeMode) {
    //     $arHTMLCacheOptions = GetCompositeOptions();
    //     EnableComposite($compositeMode === 'AUTO_COMPOSITE', $arHTMLCacheOptions);
    // }
}

UpdaterLog('FINISH UPDATE '.CURRENT_VERSION.' -> '.NEW_VERSION.PHP_EOL);
