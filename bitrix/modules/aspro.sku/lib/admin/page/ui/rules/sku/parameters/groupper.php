<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<?php

/**
 * @var Bitrix\Main\ORM\Fields\ScalarField[] $fields
 * @var array                                $data
 */

use Bitrix\Main\Localization\Loc;

return [
    'MAIN' => [
        'OPTIONS' => [
            'OFFERS_PROPERTY' => [
                'TITLE' => $fields['OFFERS_PROPERTY']->getTitle(),
                'TYPE' => 'custom',
                'CUSTOM_TYPE' => 'offers_property',
                'LIST' => $arIBlockProperties,
                'VALUE' => $data['OFFERS_PROPERTY'] ?: [],
                'REQUIRED' => $fields['OFFERS_PROPERTY']->isRequired(),
                'HINT' => Loc::getMessage('ENTITY_FIELD_OFFERS_PROPERY_HINT'),
                'MULTIPLE' => true,
                'PLACEHOLDER' => Loc::getMessage('ENTITY_FIELD_FILTER_PROPERTY_DEFAULT'),
                'DISABLED' => !$data['IBLOCK_ID'],
            ],
        ],
    ],
];
?>
