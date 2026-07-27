<?php

use Aspro\Sku\Admin\Page\Router;
use Aspro\Sku\General;
use Bitrix\Main\Localization\Loc;

AddEventHandler('main', 'OnBuildGlobalMenu', 'OnBuildGlobalMenuHandlerSku');
function OnBuildGlobalMenuHandlerSku(&$arGlobalMenu, &$arModuleMenu)
{
    if (!defined('ASPRO_SKU_MENU_INCLUDED')) {
        define('ASPRO_SKU_MENU_INCLUDED', true);

        IncludeModuleLangFile(__FILE__);
        $moduleID = 'aspro.sku';

        if (!Bitrix\Main\Loader::includeModule($moduleID)) {
            return;
        }

        $GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/'.General::assetsPath.'/menu.css');

        if ($GLOBALS['APPLICATION']->GetGroupRight($moduleID) >= 'R') {
            $arMenu = [
                'menu_id' => 'global_menu_aspro_sku',
                'text' => General::getMessage('MENU__ROOT_TEXT'),
                'title' => General::getMessage('MENU__ROOT_TITLE'),
                'sort' => 1000,
                'items_id' => 'global_menu_aspro_sku_items',
                'icon' => 'imi-sku imi-sku--root',
                'items' => [
                    [
                        'text' => General::getMessage('MENU__SETTINGS_TEXT'),
                        'title' => General::getMessage('MENU__SETTINGS_TITLE'),
                        'sort' => 10,
                        'url' => '/bitrix/admin/'.General::assetsPath.'/settings.php?lang='.urlencode(LANGUAGE_ID),
                        'more_url' => [],
                        'icon' => '',
                        'page_icon' => '',
                        'items_id' => 'menu_aspro_sku_settings',
                    ],
                    [
                        'text' => General::getMessage('MENU__RULES_TEXT'),
                        'title' => General::getMessage('MENU__RULES_TITLE'),
                        'sort' => 10,
                        'icon' => '',
                        'page_icon' => '',
                        'url' => Router::mkUrl('rules.sku', 'list'),
                        'items_id' => 'menu_aspro_sku_rules',
                        'more_url' => [
                            Router::mkUrl('rules.sku.detail', 'view'),
                        ],
                    ],
                    [
                        'text' => General::getMessage('MENU__MODULE_UPDATE_TEXT'),
                        'title' => General::getMessage('MENU__MODULE_UPDATE_TITLE'),
                        'sort' => 900,
                        'url' => '/bitrix/admin/'.General::assetsPath.'/update_module.php?lang='.urlencode(LANGUAGE_ID),
                        'more_url' => [],
                        'icon' => '',
                        'page_icon' => '',
                        'items_id' => 'menu_aspro_sku_update_module',
                    ],
                    [
                        'text' => General::getMessage('MENU__MODULE_GROUP_RIGHTS_TEXT'),
                        'title' => General::getMessage('MENU__MODULE_GROUP_RIGHTS_TITLE'),
                        'sort' => 910,
                        'url' => '/bitrix/admin/'.General::assetsPath.'/group_rights.php?lang='.urlencode(LANGUAGE_ID),
                        'icon' => 'learning_icon_groups',
                        'page_icon' => 'pi_group_rights',
                        'items_id' => 'group_rights',
                    ],
                ],
            ];

            if (!isset($arGlobalMenu['global_menu_aspro'])) {
                $arGlobalMenu['global_menu_aspro'] = [
                    'menu_id' => 'global_menu_aspro',
                    'text' => General::getMessage('MENU__GLOBAL_ASPRO_MENU_TEXT'),
                    'title' => General::getMessage('MENU__GLOBAL_ASPRO_MENU_TITLE'),
                    'sort' => 1000,
                    'items_id' => 'global_menu_aspro_items',
                ];
            }

            $arGlobalMenu['global_menu_aspro']['items'][$moduleID] = $arMenu;
        }
    }
}
