<?php

// aspro.smartseo 1.0.13 updater
// changed files

// module:
// /admin/views/seo_text/detail_element/_form_properties.php - update
// /admin/views/seo_text/detail_element/partial/property_control.php - update
// /admin/views/seo_text/detail_section/_form_properties.php - update
// /admin/views/seo_text/detail_section/partial/property_control.php - update
// /admin/views/settings/sites.php - update
// /classes/admin/settings/SettingSmartseo.php - update
// /classes/engines/SeoTextElementEngine.php - update
// /classes/engines/SeoTextEngine.php - update
// /lang/en/admin/views/settings/sites.php - update
// /lang/en/lib/condition/entities/iblock/IblockPropertyBuilder.php - add
// /lang/ru/admin/views/settings/sites.php - update
// /lang/ru/lib/condition/entities/iblock/IblockPropertyBuilder.php - add
// /lib/condition/entities/iblock/Helper.php - update
// /lib/condition/entities/iblock/IblockPropertyBuilder.php - update
// /lib/generator/handlers/PropertyUrlHandler.php - update
// /lib/models/smartseosetting.php - update
// /lib/seo/SitemapFile.php - update
// /lib/seo/SitemapIndex.php - update

// js
// /seo_text/detail_element.js - update
// /seo_text/detail_element.min.js - add
// /seo_text/detail_section.js - update
// /seo_text/detail_section.min.js - add

use Bitrix\Main\Config\Option;
use Bitrix\Main\IO;
use Bitrix\Main\Loader;

require_once __DIR__.'/functions.php';

define('PARTNER_NAME', 'aspro');
define('MODULE_NAME', 'aspro.smartseo');
define('MODULE_NAME_SHORT', 'smartseo');
define('TEMPLATE_NAME', 'aspro_smartseo');
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
    // module
    MODULE_PATH.'/admin/views/seo_text/detail_element/_form_properties.php',
    MODULE_PATH.'/admin/views/seo_text/detail_element/partial/property_control.php',
    MODULE_PATH.'/admin/views/seo_text/detail_section/_form_properties.php',
    MODULE_PATH.'/admin/views/seo_text/detail_section/partial/property_control.php',
    MODULE_PATH.'/admin/views/settings/sites.php',
    MODULE_PATH.'/classes/admin/settings/SettingSmartseo.php',
    MODULE_PATH.'/classes/engines/SeoTextElementEngine.php',
    MODULE_PATH.'/classes/engines/SeoTextEngine.php',
    MODULE_PATH.'/lang/en/admin/views/settings/sites.php',
    MODULE_PATH.'/lang/ru/admin/views/settings/sites.php',
    MODULE_PATH.'/lib/condition/entities/iblock/Helper.php',
    MODULE_PATH.'/lib/condition/entities/iblock/IblockPropertyBuilder.php',
    MODULE_PATH.'/lib/generator/handlers/PropertyUrlHandler.php',
    MODULE_PATH.'/lib/models/smartseosetting.php',
    MODULE_PATH.'/lib/seo/SitemapFile.php',
    MODULE_PATH.'/lib/seo/SitemapIndex.php',

    // js
    ADMIN_JS_PATH.'/seo_text/detail_element.js',
    ADMIN_JS_PATH.'/seo_text/detail_section.js',

] as $file) {
    CreateBakFile($_SERVER['DOCUMENT_ROOT'].$file);
}

if (Loader::includeModule(MODULE_NAME)) {
    // update admin section images
    // CopyDirFiles(__DIR__.'/install/images', $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/'.MODULE_NAME.'/', true, true);

    // update admin page
    // CopyDirFiles(__DIR__.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.MODULE_NAME.'/', true, true);

    // update admin js
    CopyDirFiles(__DIR__.'/install/js', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.MODULE_NAME.'/', true, true);

    // update admin css
    // CopyDirFiles(__DIR__.'/install/css', $_SERVER['DOCUMENT_ROOT'].'/bitrix/css/'.MODULE_NAME.'/', true, true);

    // update admin tools
    // CopyDirFiles(__DIR__.'/install/tools', $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.MODULE_NAME.'/', true, true);

    // update components
    if (IO\Directory::isDirectoryExists($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.PARTNER_NAME.'/')) {
        // CopyDirFiles(__DIR__.'/install/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/', true, true);
    }

    // current SITEs
    // $arSites = GetSites();

    // current IBLOCK_IDs
    // $arIblocks = GetIBlocks();

    // is composite enabled
    // $compositeMode = IsCompositeEnabled();

    // clear all sites cache in some components and dirs (include composite cache)
    // ClearAllSitesCacheDirs([
    // 	'html_pages',
    // 	'cache/js',
    // 	'cache/css'
    // ]);

    // SEO-206
    Option::set(MODULE_NAME, 'NEW_URL_REPLACE_SPECIAL_CHAR_STRING','N');

    // ClearAllSitesCacheComponents([
    // 	"aspro:smartseo.tags"
    // ]);

    // if ($compositeMode) {
    // 	$arHTMLCacheOptions = GetCompositeOptions();
    // 	EnableComposite($compositeMode === 'AUTO_COMPOSITE', $arHTMLCacheOptions);
    // }
}

UpdaterLog('FINISH UPDATE '.CURRENT_VERSION.' -> '.NEW_VERSION.PHP_EOL);
