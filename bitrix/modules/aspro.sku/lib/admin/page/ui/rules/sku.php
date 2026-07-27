<?php

namespace Aspro\Sku\Admin\Page\Ui\Rules;

use Aspro\Sku\Admin\Controller\IBlock as IBlockController;
use Aspro\Sku\Admin\Page\Router;
use Aspro\Sku\Admin\Page\Ui\Section;
use Aspro\Sku\Enums\RulesSkuFilterType;
use Aspro\Sku\General;
use Aspro\Sku\ORM\RulesSkuTable;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\Web\Json;

class Sku extends Section
{
    public function getFilterFields(): array
    {
        $arDisplay = ['ID', 'ACTIVE', 'NAME', 'SITE_ID', 'IBLOCK_ID', 'FILTER_TYPE'];
        $arDefault = ['ACTIVE', 'NAME', 'SITE_ID', 'IBLOCK_ID', 'FILTER_TYPE'];
        $arTypesMap = [
            'Bitrix\Main\ORM\Fields\IntegerField' => 'number',
            'Bitrix\Main\ORM\Fields\DatetimeField' => 'date',
            'Bitrix\Main\ORM\Fields\BooleanField' => 'list',
            'Bitrix\Main\ORM\Fields\EnumField' => 'list',
        ];

        return array_map(fn (Field $field) => [
            'id' => $field->getColumnName(),
            'name' => $field->getTitle(),
            'type' => $this->getFilterType($field),
            'items' => $this->getValuesForFilter($field),
            'default' => in_array($field->getColumnName(), $arDefault) ? true : false,
        ], array_filter(RulesSkuTable::getMap(), fn (Field $field) => in_array($field->getColumnName(), $arDisplay)));
    }

    private function getValuesForFilter(Field $field): array
    {
        if (!$this->isListableField($field)) {
            return [];
        }

        if ($field->getColumnName() === 'FILTER_TYPE') {
            return RulesSkuFilterType::getNamesWithLang();
        }

        if ($field->getColumnName() === 'SITE_ID') {
            return IBlockController::getSites();
        }

        $arValues = [];
        foreach ($field->getValues() as $value) {
            $arValues[$value] = General::getMessage('UI_VALUE_'.$value);
        }

        return $arValues;
    }

    private function isListableField(Field $field): bool
    {
        return method_exists($field, 'getValues') || $field->getColumnName() === 'SITE_ID';
    }

    private function getFilterType(Field $field)
    {
        if ($field->getColumnName() === 'SITE_ID') {
            return 'list';
        }

        $arTypesMap = [
            'Bitrix\Main\ORM\Fields\IntegerField' => 'number',
            'Bitrix\Main\ORM\Fields\DatetimeField' => 'date',
            'Bitrix\Main\ORM\Fields\BooleanField' => 'list',
            'Bitrix\Main\ORM\Fields\EnumField' => 'list',
        ];

        return $arTypesMap[$field::class];
    }

    public function getContextMenu($urlParams = []): array
    {
        return [
            [
                'TEXT' => General::getMessage('CONTEXT_MENU_ADD_RULE'),
                'ICON' => '',
                'LINK' => Router::mkUrl('rules.sku.detail', 'view', $urlParams),
                // 'LINK' => BX_ROOT.'/admin/'.General::assetsPath.'/rules/sku/detail.php',
                'MENU' => $items,
            ],
        ];
    }

    public function getGridColumns(): array
    {
        $arDefault = ['ID', 'ACTIVE', 'NAME', 'SITE_ID', 'IBLOCK_ID', 'FILTER_TYPE'];
        $arHide = ['FILTER_ITEMS', 'FILTER_SECTION_ID', 'FILTER_PROPERTY', 'OFFERS_PROPERTY'];

        return array_map(fn (Field $field) => [
            'id' => $field->getColumnName(),
            'title' => $field->getTitle(),
            'content' => $field->getTitle(),
            'sort' => $field->getColumnName(),
            'default' => in_array($field->getColumnName(), $arDefault) ? true : false,
        ], array_filter(RulesSkuTable::getMap(), fn (Field $field) => !in_array($field->getColumnName(), $arHide)));
    }

    public function getMoreActionsInRow($controllerName, array $row): array
    {
        return [
            [
                'TEXT' => General::getMessage('UI_ACTION_COPY'),
                'ACTION' => "BX.adminPanel.Redirect([], '".Router::mkUrl($controllerName.'.detail', 'view', ['id' => $row['ID'], 'copy' => 'Y'])."', event)'",
                'DEFAULT' => true,
            ],
            [
                'TEXT' => General::getMessage('UI_ACTION_'.($row['ACTIVE'] === 'Y' ? 'DEACTIVATE' : 'ACTIVATE')),
                'ACTION' => "BX.Aspro.Sku.GridManager.send('".Router::mkUrl($controllerName, 'update')."', $row[ID], ".Json::encode(['FIELDS' => ['ACTIVE' => $row['ACTIVE'] === 'Y' ? 'N' : 'Y']]).')',
                'DEFAULT' => true,
            ],
            [
                'TEXT' => General::getMessage('UI_ACTION_DELETE'),
                'ACTION' => "BX.Aspro.Sku.GridManager.sendWithConfirm('".Router::mkUrl($controllerName, 'delete')."', $row[ID])",
                'DEFAULT' => true,
            ],
        ];
    }

    public function getMoreGroupActions(): array
    {
        return [
            'activate' => General::getMessage('UI_ACTION_ACTIVATE'),
            'deactivate' => General::getMessage('UI_ACTION_DEACTIVATE'),
            'copy' => General::getMessage('UI_ACTION_COPY'),
        ];
    }
}
