<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$arExtensions = ['mega_menu', 'header.menu.left_catalog.aim'];
if ($templateData['USE_AJAX_SUBMENU']) {
    $arExtensions[] = 'header.menu.left_catalog.ajax_submenu';
}

TSolution\Extensions::init($arExtensions);
