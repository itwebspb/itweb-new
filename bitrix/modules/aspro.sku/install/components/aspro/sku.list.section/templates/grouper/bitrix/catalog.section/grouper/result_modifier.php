<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

if (empty($arResult['ITEMS'])) {
    return;
}

$arResult['PROPS'] = Aspro\Sku\Engine::build(items: $arResult['ITEMS']);
