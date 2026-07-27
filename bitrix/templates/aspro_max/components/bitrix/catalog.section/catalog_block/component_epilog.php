<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$arExtensions = ['countdown', 'bonus_system', 'gesture'];

//	big data json answers
if (isset($arParams['BIG_DATA_MODE']) && $arParams['BIG_DATA_MODE'] === 'Y') {
    $request = Bitrix\Main\Context::getCurrent()->getRequest();
    if ($request->isAjaxRequest() && ($request->get('action') === 'deferredLoad')) {
        $content = ob_get_contents();
        ob_end_clean();

        list(, $itemsContainer) = explode('<!-- items-container -->', $content);

        $component::sendJsonAnswer([
            'items' => $itemsContainer,
        ]);
    }
    $arExtensions[] = 'bigdata';
}

if (isset($arParams['SLIDE_ITEMS']) && $arParams['SLIDE_ITEMS']) {
    $arExtensions[] = 'swiper';

    echo "<script>BX.ready(() => typeof initSwiperSlider === 'function' && initSwiperSlider());</script>";
}

if (TSolution::GetFrontParametrValue('HOVER_TYPE_IMG') !== 'none') {
    $arExtensions[] = 'animation_ext';
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
if (count($arExtensions)) {
    TSolution\Extensions::initInPopup($arExtensions);
}
