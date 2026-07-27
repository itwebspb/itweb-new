<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<?php

/**
 * @var Bitrix\Main\ORM\Fields\ScalarField[] $fields
 * @var array                                $data
 * @var array                                $arIBlockProperties
 * @var array                                $arIBlocks
 * @var array                                $arLinkedItems
 * @var array                                $arSections
 * @var array                                $arSites
 */

use Aspro\Sku\Enums\RulesSkuFilterType;
use Bitrix\Main\Localization\Loc;

$isManualFilterHidden = $data['FILTER_TYPE'] === 'FILTER' || (
    !$data['FILTER_TYPE'] && $fields['FILTER_TYPE']->getDefaultValue() == 'FILTER'
);

return [
    'MAIN' => [
        'OPTIONS' => [
            'SITE_ID' => [
                'TITLE' => $fields['SITE_ID']->getTitle(),
                'TYPE' => 'selectbox',
                'LIST' => $arSites,
                'VALUE' => $data['SITE_ID'],
                'DEFAULT' => $data['SITE_ID'],
                'REQUIRED' => $fields['SITE_ID']->isRequired(),
                'HINT' => Loc::getMessage('ENTITY_FIELD_SITE_ID_HINT'),
                'PLACEHOLDER' => Loc::getMessage('ENTITY_FIELD_SITE_ID_DEFAULT'),
            ],
            'IBLOCK_ID' => [
                'TITLE' => $fields['IBLOCK_ID']->getTitle(),
                'TYPE' => 'selectbox',
                'LIST' => $arIBlocks,
                'VALUE' => $data['IBLOCK_ID'],
                'REQUIRED' => $fields['IBLOCK_ID']->isRequired(),
                'HINT' => Loc::getMessage('ENTITY_FIELD_IBLOCK_ID_HINT'),
                'PLACEHOLDER' => Loc::getMessage('ENTITY_FIELD_IBLOCK_ID_DEFAULT'),
            ],
            'FILTER_TYPE' => [
                'TITLE' => $fields['FILTER_TYPE']->getTitle(),
                'TYPE' => 'radio',
                'LIST' => RulesSkuFilterType::getNamesWithLang(),
                'VALUE' => $data['FILTER_TYPE'],
                'REQUIRED' => $fields['FILTER_TYPE']->isRequired(),
                'HINT' => Loc::getMessage('ENTITY_FIELD_FILTER_TYPE_HINT'),
                'DEFAULT' => $fields['FILTER_TYPE']->getDefaultValue(),
                'IS_DISABLED' => !$data['IBLOCK_ID'],
            ],
            'FILTER_ITEMS' => [
                'TITLE' => $fields['FILTER_ITEMS']->getTitle(),
                'TYPE' => 'custom',
                'CUSTOM_TYPE' => 'link_elements',
                'LINK_IBLOCK_ID' => $data['IBLOCK_ID'],
                'LIST' => $arLinkedItems,
                'REQUIRED' => $fields['FILTER_ITEMS']->isRequired(),
                'HINT' => Loc::getMessage('ENTITY_FIELD_FILTER_ITEMS_HINT'),
                'HIDDEN' => $isManualFilterHidden,
                'DISABLED' => !$data['IBLOCK_ID'],
                'TITLE_CLASS' => 'adm-detail-valign-top',
            ],
            'FILTER_SECTION_ID' => [
                'TITLE' => $fields['FILTER_SECTION_ID']->getTitle(),
                'TYPE' => 'multiselectbox',
                'LIST' => $arSections,
                'VALUE' => $data['FILTER_SECTION_ID'],
                'REQUIRED' => $fields['FILTER_SECTION_ID']->isRequired(),
                'HINT' => Loc::getMessage('ENTITY_FIELD_FILTER_SECTION_ID_HINT'),
                'HIDDEN' => !$isManualFilterHidden,
                'DISABLED' => !$data['IBLOCK_ID'] || empty($arSections),
                'PLACEHOLDER' => Loc::getMessage('ENTITY_FIELD_FILTER_SECTION_DEFAULT'),
            ],
            'FILTER_PROPERTY' => [
                'TITLE' => $fields['FILTER_PROPERTY']->getTitle(),
                'TYPE' => 'multiselectbox',
                'LIST' => $arIBlockProperties,
                'VALUE' => $data['FILTER_PROPERTY'],
                'REQUIRED' => $fields['FILTER_PROPERTY']->isRequired(),
                'HINT' => Loc::getMessage('ENTITY_FIELD_FILTER_PROPERTY_HINT'),
                'HIDDEN' => !$isManualFilterHidden,
                'DISABLED' => !$data['IBLOCK_ID'],
                'PLACEHOLDER' => Loc::getMessage('ENTITY_FIELD_FILTER_PROPERTY_DEFAULT'),
            ],
        ],
    ],
];
