<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$arPrice = array();

if (\Bitrix\Main\Loader::includeModule('catalog')) {
    $arPrice = CCatalogIBlockParameters::getPriceTypesList();
}

$arTemplateParameters = array(
    'SHOW_DETAIL_LINK' => array(
        'NAME' => GetMessage('SHOW_DETAIL_LINK'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'Y',
    ),
    'HIDE_SECTION_NAME' => array(
        'NAME' => GetMessage('T_HIDE_SECTION_NAME'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'N',
    ),
    "TITLE_BLOCK" => array(
        "NAME" => GetMessage("T_TITLE"),
        "TYPE" => "STRING",
        "DEFAULT" => GetMessage("BLOCK_NAME"),
    ),
    "TITLE_BLOCK_ALL" => array(
        "NAME" => GetMessage("TITLE_BLOCK_ALL_NAME"),
        "TYPE" => "STRING",
        "DEFAULT" => GetMessage("BLOCK_ALL_NAME"),
    ),
    "ALL_URL" => array(
        "NAME" => GetMessage("T_ALL_URL"),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    'ELEMENT_IN_ROW' => array(
        'PARENT' => 'LIST_SETTINGS',
        'NAME' => GetMessage('T_SECTION-ELEMENTS_ELEMENTS_COUNT'),
        'TYPE' => 'LIST',
        'VALUES' => array(
            'FROM_MODULE' => GetMessage('FROM_MODULE_PARAMS'),
            '3' => GetMessage('3'),
            '4' => GetMessage('4'),
        ),
        'DEFAULT' => 'FROM_MODULE',
    ),
    'PRICE_VAT_INCLUDE'=> [
        "PARENT" => "DETAIL_SETTINGS",
		"NAME" => GetMessage("IBLOCK_PRICE_VAT_INCLUDE"),
		"TYPE" => "CHECKBOX",
		"REFRESH" => "N",
		"DEFAULT" => "Y",
    ],
    'PRICE_CODE' => [
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('PRICE_CODE_TITLE'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'VALUES' => $arPrice,
    ],
    'SHOW_PROPS' => [
        'NAME' => GetMessage('SHOW_PROPS_TITLE'),
		"TYPE" => "CHECKBOX",
		'PARENT' => 'DETAIL_SETTINGS',
		"REFRESH" => "N",
		"DEFAULT" => "Y",
    ],
);
