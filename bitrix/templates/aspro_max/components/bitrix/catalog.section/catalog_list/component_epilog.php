<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

if (isset($templateData['TEMPLATE_LIBRARY']) && !empty($templateData['TEMPLATE_LIBRARY'])) {
    CJSCore::Init($templateData['TEMPLATE_LIBRARY']);

    $loadCurrency = false;
    if (!empty($templateData['CURRENCIES'])) {
        $loadCurrency = Bitrix\Main\Loader::includeModule('currency');
    }

    if ($loadCurrency) {
        echo '<script>BX.Currency.setCurrencies('.$templateData['CURRENCIES'].');</script>';
    }
}

echo "<script>typeof useCountdown === 'function' && useCountdown();</script>";

TSolution\Extensions::initInPopup(['countdown', 'bonus_system', 'ikSelect', 'gesture']);
