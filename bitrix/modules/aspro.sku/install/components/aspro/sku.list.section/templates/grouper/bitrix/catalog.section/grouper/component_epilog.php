<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

if ($templateData['HAS_PROPS']) {
    \Bitrix\Main\UI\Extension::load('aspro.sku.template');

    if (!defined($arParams['COMPONENT_MARKER'])) {
        define($arParams['COMPONENT_MARKER'], true);
    }
}
