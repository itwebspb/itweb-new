<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<?php

/**
 * @var Bitrix\Main\ORM\Fields\ScalarField[] $fields
 * @var array                                $data
 */

use Aspro\Sku\Enums\ActivateTypeRules;
use Aspro\Sku\Enums\ProfileEarnTypes;
use Aspro\Sku\General;
use Bitrix\Main\Localization\Loc;

return [
    'MAIN' => [
        'OPTIONS' => [
            'ID' => [
                'TITLE' => $fields['ID']->getTitle(),
                'TYPE' => 'readonly',
                'DEFAULT' => $data['ID'] ?? 'new',
                'VALUE' => $data['ID'],
            ],
            'ACTIVE' => [
                'TITLE' => $fields['ACTIVE']->getTitle(),
                'TYPE' => 'checkbox',
                'DEFAULT' => $fields['ACTIVE']->getDefaultValue(),
                'VALUE' => $data['ACTIVE'],
            ],
            'NAME' => [
                'TITLE' => $fields['NAME']->getTitle(),
                'TYPE' => 'text',
                'REQUIRED' => $fields['NAME']->isRequired(),
                'DEFAULT' => $data['NAME'],
            ],
            'SORT' => [
                'TITLE' => $fields['SORT']->getTitle(),
                'TYPE' => 'text',
                'REQUIRED' => $fields['SORT']->isRequired(),
                'DEFAULT' => $data['SORT'] ?? $fields['SORT']->getDefaultValue(),
            ],
        ],
    ],
];
?>
