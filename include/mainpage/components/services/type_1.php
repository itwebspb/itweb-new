<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $arRegion;

$indexPageOptions = $GLOBALS['arTheme']['INDEX_TYPE']['SUB_PARAMS'][$GLOBALS['arTheme']['INDEX_TYPE']['VALUE']];
$blockOptions = $indexPageOptions['SERVICES'];
$blockTemplateOptions = $blockOptions['TEMPLATE']['LIST'][$blockOptions['TEMPLATE']['VALUE']];

$bShowMore = $blockTemplateOptions['ADDITIONAL_OPTIONS']['LINES_COUNT']['VALUE'] === 'SHOW_MORE';
$linesCount = $bShowMore ? 1 : (intval($blockTemplateOptions['ADDITIONAL_OPTIONS']['LINES_COUNT']['VALUE']) ?: 1);
$elementInRow = $blockTemplateOptions['ADDITIONAL_OPTIONS']['ELEMENTS_COUNT']['VALUE'];

?>
<?$APPLICATION->IncludeComponent(
	"bitrix:news.list",
	"content-list-blocks",
	[
        "IBLOCK_ID" => "21",
        "IBLOCK_TYPE" => "aspro_max_content",
		"DEPTH_LEVEL" => "1",
		"CHECK_REQUEST_BLOCK" => CMax::checkRequestBlock("services"),
		"IS_AJAX" => CMax::checkAjaxRequest(),
		"NEWS_COUNT" => $linesCount*$elementInRow,
		"COMPONENT_TEMPLATE" => "content-list-blocks",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_ORDER1" => "DESC",
		"SORT_BY2" => "SORT",
		"SORT_ORDER2" => "ASC",
		"FILTER_NAME" => "",
		"FIELD_CODE" => [
			0 => "NAME",
			1 => "PREVIEW_TEXT",
			2 => "PREVIEW_PICTURE",
			3 => "DETAIL_TEXT",
			4 => "DETAIL_PICTURE",
			5 => "",
		],
		"PROPERTY_CODE" => [
			0 => "FORM_ORDER",
			1 => "PRICE_OLD",
			2 => "PRICE",
			3 => "PROP_3",
			4 => "PROP_1",
			5 => "PROP_2",
			6 => "",
		],
		"CHECK_DATES" => "Y",
        'FILTER_NAME' => 'arRegionLinkFront',
		"DETAIL_URL" => "",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"PREVIEW_TRUNCATE_LEN" => "",
		"ACTIVE_DATE_FORMAT" => "j F Y",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_LAST_MODIFIED" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"INCLUDE_SUBSECTIONS" => "N",
		"STRICT_SECTION_CHECK" => "N",
		"SHOW_DETAIL_LINK" => "Y",
		"USE_SHARE" => "N",
		"HIDE_SECTION_NAME" => "Y",
		"PAGER_TEMPLATE" => "ajax",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Услуги",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => "",
		"MAXWIDTH_WRAP" => "Y",
		"TITLE_BLOCK" => "Услуги",
		"TITLE_BLOCK_ALL" => "Все Услуги",
		"ALL_URL" => "services/",
		"ELEMENT_IN_ROW" => "FROM_MODULE",
		"BORDERED" => "Y",
		"PRICE_VAT_INCLUDE" => "Y",
		"SERVICES_MODE" => "Y",
		"HIDE_PAGINATION" => $bShowMore?"N":"Y",
		"MOBILE_SCROLLED" => "Y",
        "SHOW_PROPS" => "N",
		"PRICE_CODE" => [
			0 => "BASE",
			1 => "MSC",
            2 => "EKB",
			3 => "MAGNITOGORSK",
            4 => "OPT",
		],
		"THEME" => [
			"ELEMENT_IN_ROW" => $elementInRow??null,
		]
	],
	false
);?>
