<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

if (!$arResult['FILTERED_ITEMS']) {
    return;
}

$GLOBALS[$component::AVAILABILITY_FILTER_NAME] = [
    'ID' => $arResult['FILTERED_ITEMS'],
];

$APPLICATION->IncludeComponent(
    'bitrix:catalog.section',
    $this->getName(),
    $component->getSectionComponentParams($arResult['PROP_CODES']),
    $component,
    ['HIDE_ICONS' => 'Y']
);
