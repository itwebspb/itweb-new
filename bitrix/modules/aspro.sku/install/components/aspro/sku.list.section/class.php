<?php

use Aspro\Sku\Components\List\Base as SkuListBase;
use Aspro\Sku\Engine;
use Bitrix\Main\Config\Option;

class SkuListSection extends SkuListBase
{
    public const AVAILABILITY_FILTER_NAME = 'SKU_LIST_AVAILABLE';

    public function onPrepareComponentParams($arParams)
    {
        $arParams['USE_AVAILABILITY'] = true;
        $arParams['USE_FEATURE_PROPS'] = Option::get('iblock', 'property_features_enabled', 'Y') === 'Y';

        return $arParams;
    }

    public function executeComponent()
    {
        if ($this->startResultCache(false)) {
            $this->arResult = Engine::buildItemsAndPropCodes($this->arParams, $this->getParent()->arParams);

            $this->endResultCache();
        }

        Engine::refreshContextFromCache($this->arParams, $this->getParent()->arParams, $this->arResult['FILTERED_ITEMS'], $this->arResult['PROP_CODES']);

        $this->IncludeComponentTemplate();
    }

    public function getSectionComponentParams(?array $propCodes = [])
    {
        $params = array_merge($this->getCurrentComponentParams(), $this->getParentComponentParams());
        if ($propCodes) {
            $params['PROPERTY_CODE'] = $propCodes;
        }

        $parentComponentParams = $this->getParent()->arParams ?? [];
        if ($parentComponentParams['FILTER_NAME']) {
            $GLOBALS[$params['FILTER_NAME']] = array_merge(
                (array) $GLOBALS[$params['FILTER_NAME']],
                (array) $GLOBALS[$parentComponentParams['FILTER_NAME']]
            );
        }

        return $params;
    }

    private function getCurrentComponentParams(): array
    {
        return [
            'USE_REGION' => $this->arParams['USE_REGION'],
            'CACHE_TIME' => $this->arParams['CACHE_TIME'],
            'CACHE_TYPE' => $this->arParams['CACHE_TYPE'],
            'COMPONENT_MARKER' => $this->arParams['COMPONENT_MARKER'],
            'ELEMENT_SORT_FIELD' => $this->arParams['SKU_SORT_FIELD'],
            'ELEMENT_SORT_ORDER' => $this->arParams['SKU_SORT_ORDER'],
            'ELEMENT_SORT_FIELD2' => $this->arParams['SKU_SORT_FIELD2'],
            'ELEMENT_SORT_ORDER2' => $this->arParams['SKU_SORT_ORDER2'],
            'FILTER_NAME' => self::AVAILABILITY_FILTER_NAME,
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'USE_FEATURE_PROPS' => $this->arParams['USE_FEATURE_PROPS'],
        ];
    }

    private function getParentComponentParams(): array
    {
        $parentComponentParams = $this->getParent()->arParams ?? [];

        $params = $this->getSectionComponentContantParams();
        foreach ($this->getParentComponentParamsList() as $param) {
            $params[$param] = $parentComponentParams[$param];
        }

        return $params;
    }

    private function getSectionComponentContantParams(): array
    {
        return [
            'ADD_SECTIONS_CHAIN' => 'N',
            'COMPATIBLE_MODE' => 'Y',
            'DISPLAY_BOTTOM_PAGER' => 'N',
            'DISPLAY_TOP_PAGER' => 'N',
            'HIDE_NOT_AVAILABLE' => 'N',
            'IBINHERIT_TEMPLATES' => [],
            'INCLUDE_SUBSECTIONS' => 'Y',
            'PAGE_ELEMENT_COUNT' => '999',
            'PAGER_BASE_LINK_ENABLE' => 'Y',
            'PAGER_SHOW_ALWAYS' => 'N',
            'PAGER_TITLE' => '',
            'PARTIAL_PRODUCT_PROPERTIES' => 'N',
            'SECTION_COUNT_ELEMENTS' => 'N',
            'SET_LAST_MODIFIED' => 'N',
            'SET_STATUS_404' => 'N',
            'SET_TITLE' => 'N',
            'SHOW_404' => 'N',
        ];
    }

    private function getParentComponentParamsList(): array
    {
        return [
            'ACTION_VARIABLE',
            'ADD_CHAIN_ITEM',
            'AJAX_OPTION_ADDITIONAL',
            'BASKET_URL',
            'CACHE_FILTER',
            'CACHE_GROUPS',
            'CONVERT_CURRENCY',
            'CURRENCY_ID',
            'CURRENT_BASE_PAGE',
            'DEFAULT_COUNT',
            'DETAIL_URL',
            'FIELDS',
            'FILE_404',
            'IS_CATALOG_PAGE',
            'MAX_AMOUNT',
            'MESSAGE_404',
            'MIN_AMOUNT',
            'PAGER_BASE_LINK',
            'PRICE_CODE',
            'PRICE_VAT_INCLUDE',
            'PRODUCT_ID_VARIABLE',
            'PRODUCT_PROPERTIES',
            'PRODUCT_PROPS_VARIABLE',
            'PRODUCT_QUANTITY_VARIABLE',
            'SECTION_ID_VARIABLE',
            'SECTION_URL',
            'SEF_MODE',
            'SEF_URL_TEMPLATES',
            'SHOW_COUNTER_LIST',
            'SHOW_DISCOUNT_PERCENT_NUMBER',
            'SHOW_DISCOUNT_PERCENT',
            'SHOW_DISCOUNT_TIME',
            'SHOW_MEASURE',
            'SHOW_OLD_PRICE',
            'SHOW_PRICE_COUNT',
            'SHOW_QUANTITY_COUNT',
            'SHOW_QUANTITY',
            'SHOW_UNABLE_SKU_PROPS',
            'STORES',
            'USE_MAIN_ELEMENT_SECTION',
            'USE_MIN_AMOUNT',
            'USE_ONLY_MAX_AMOUNT',
            'USE_PRICE_COUNT',
            'USE_PRODUCT_QUANTITY',
            'USE_STORE',
            'USER_FIELDS',
        ];
    }
}
