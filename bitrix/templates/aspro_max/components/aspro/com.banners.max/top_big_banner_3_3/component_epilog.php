<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

if (!isset($templateData['HAS_ITEMS'])) {
    return;
}

$arExtensions = ['swiper', 'swiper_main_styles', 'top_banner', 'countdown'];
if ($templateData['HAS_VIDEO']) {
    $arExtensions[] = 'video_banner';
}
TSolution\Extensions::init($arExtensions);

include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/aspro/com.banners.max/common_files/epilog_action.php';
