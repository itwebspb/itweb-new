<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

use Aspro\Sku\General;

return [
    'MAIN' => [
        'OPTIONS' => [
            'ENABLED' => [
                'TITLE' => General::getMessage('ENABLED'),
                'TYPE' => 'checkbox',
                'DEFAULT' => 'Y',
            ],
            'SHOW_TEXT_FOR_EMPTY_PICTURES' => [
                'TITLE' => General::getMessage('SHOW_TEXT_FOR_EMPTY_PICTURES'),
                'TYPE' => 'checkbox',
                'DEFAULT' => 'Y',
            ],
            'SHOW_HINTS' => [
                'TITLE' => General::getMessage('SHOW_HINTS'),
                'TYPE' => 'checkbox',
                'DEFAULT' => 'Y',
            ],
            'SKU_SHOW_PREVIEW_PICTURE_PROPS' => [
                'TITLE' => General::getMessage('SKU_SHOW_PREVIEW_PICTURE_PROPS'),
                'TYPE' => 'datalist',
                'DEFAULT' => '',
                'HINT' => General::getMessage('SKU_SHOW_PREVIEW_PICTURE_PROPS_HINT'),
            ],
            'USE_AVAILABILITY' => [
                'TITLE' => General::getMessage('USE_AVAILIABILITY'),
                'TYPE' => 'checkbox',
                'DEFAULT' => 'N',
                'HINT' => General::getMessage('USE_AVAILIABILITY_HINT'),
            ],
            'USE_AVAILABILITY_NOTE' => [
                'TYPE' => 'note',
                'NOTE' => General::getMessage('USE_AVAILIABILITY_NOTE'),
            ],
        ],
    ],
];
