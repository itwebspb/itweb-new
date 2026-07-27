<?php

namespace Aspro\Sku\Admin\Page\Ui\Rules\Sku;

use Aspro\Sku\Admin\Controller\IBlock as IBlockController;
use Aspro\Sku\Admin\Page\Router;
use Aspro\Sku\Admin\Page\Ui\Detail as UiDetail;
use Aspro\Sku\General;
use Bitrix\Main\Localization\Loc;

class Detail extends UiDetail
{
    public function getContextMenu(array $params = []): array
    {
        $menu = [
            [
                'TEXT' => General::getMessage('TOOLBAR_MENU_ADD_LIST'),
                'LINK' => Router::mkUrl('rules.sku', 'list'),
                'ICON' => 'btn_list',
            ],
        ];
        if ($params['hasRightsToWrite'] && !$params['isCopyAction']) {
            $menu[] = [
                'TEXT' => General::getMessage('TOOLBAR_RULE_SKU_MENU_ADD'),
                'LINK' => Router::mkUrl('rules.sku.detail', $params['action']),
                'ICON' => 'btn_new',
            ];
            if ($params['data']) {
                $menu = array_merge($menu, [
                    [
                        'TEXT' => General::getMessage('TOOLBAR_RULE_SKU_MENU_COPY'),
                        'LINK' => Router::mkUrl('rules.sku.detail', $params['action'], ['id' => $params['data']['ID'], 'copy' => 'Y']),
                        'ICON' => 'btn_copy',
                    ],
                    [
                        'TEXT' => General::getMessage('TOOLBAR_RULE_SKU_MENU_DELETE'),
                        'LINK' => '',
                        'ICON' => 'btn_delete',
                        'ONCLICK' => 'new BX.Aspro.Sku.Popup.Confirm({url: \''.Router::mkUrl('rules.sku', 'delete').'\', data: { ID: '.$params['data']['ID'].', redirect: \''.Router::mkUrl('rules.sku', 'list').'\'}})',
                    ],
                ]);
            }
        }

        return $menu;
    }

    public function getTabs(array $fields = [], array $data = [], string $action = ''): array
    {
        $arIBlocks = $arIBlockProperties = $arSections = $arLinkedItems = [];

        $arSites = IBlockController::getSites();

        $data['SITE_ID'] = $data['SITE_ID'] ?: key($arSites);
        if ($data['SITE_ID']) {
            $arIBlocks = IBlockController::getIBlocks($data['SITE_ID']);
        }

        if ($data['IBLOCK_ID']) {
            $arIBlockProperties = IBlockController::getIBlockProperties($data['IBLOCK_ID']);
            $arSections = IBlockController::getSections($data['IBLOCK_ID']);

            if ($data['FILTER_ITEMS']) {
                $arLinkedItems = IBlockController::getLinkedItems($data['IBLOCK_ID'], $data['FILTER_ITEMS']);
            }
        }

        $tabs = [
            [
                'DIV' => 'edit_general',
                'TAB' => General::getMessage('TAB_MAIN'),
                'TYPE' => 'ORM',
                'OPTIONS' => include_once ('parameters/common.php'),
            ],
            [
                'DIV' => 'edit_rules',
                'TAB' => General::getMessage('TAB_RULES'),
                'TYPE' => 'ORM',
                'OPTIONS' => include_once ('parameters/rules.php'),
            ],
            [
                'DIV' => 'edit_groupper',
                'TAB' => General::getMessage('TAB_GROUPPER'),
                'TYPE' => 'ORM',
                'OPTIONS' => include_once ('parameters/groupper.php'),
            ],
        ];

        return $tabs;
    }

    protected function getTabButtonBack(): string
    {
        ob_start(); ?>

        <a href="<?= Router::mkUrl('rules.sku', 'list'); ?>" class="ui-btn ui-btn-link" title="<?= General::getMessage('UI_ACTION_CANCEL_TITLE'); ?>">
            <?= General::getMessage('UI_ACTION_CANCEL'); ?>
        </a>

        <?$buttons = ob_get_clean();

        return $buttons;
    }
}
