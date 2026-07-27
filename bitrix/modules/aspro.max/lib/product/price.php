<?php

namespace Aspro\Max\Product;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use CMax as Solution;

class Price
{
    public static function isRangePriceMode(array $result = [])
    {
        return isset($result['ITEM_PRICE_MODE']) && $result['ITEM_PRICE_MODE'] === 'Q';
    }

    public static function resolveWhenEmptyPriceMatrix(array $result = [], array $element = [], array $params = [])
    {
        $arResult = [];
        $prices = $result['CAT_PRICES'] ?? $result['PRICES'];

        if (!Loader::includeModule('catalog')) {
            throw new SystemException('Error include catalog');
        }

        if (!$prices) {
            return $arResult;
        }

        if (!$element) {
            $element = $result;
        }

        $isEmptyPriceMatrix = isset($element['PRICE_MATRIX']) && !$element['PRICE_MATRIX'];
        if (
            $isEmptyPriceMatrix
            && !$element['PRICES']
        ) {
            $arFilter = [
                'IBLOCK_ID' => $element['IBLOCK_ID'],
                '=ID' => $element['ID'],
            ];
            $arSelect = array_map(fn ($item) => 'CATALOG_GROUP_'.$item['ID'], $prices);
            $arElement = \CIBLockElement::GetList(['ID' => 'DESC'], $arFilter, false, false, $arSelect)->Fetch();

            $arResult['PRICES'] = \CIBlockPriceTools::GetItemPrices(
                $element['IBLOCK_ID'],
                $prices,
                array_merge($result, $arElement),
                $params['PRICE_VAT_INCLUDE'],
                $result['CONVERT_CURRENCY']
            );
            if (!empty($arResult['PRICES'])) {
                $arResult['MIN_PRICE'] = \CIBlockPriceTools::getMinPriceFromList($arResult['PRICES']);
            }
        }

        return $arResult;
    }

    public static function getValue(array $arItem) {

        $price = $arItem['MIN_PRICE']['DISCOUNT_VALUE']
            ?? $arItem['MIN_PRICE']['VALUE']
            ?? '0';

        return $price;
    }

    public static function getCurrency() {

        $currencyDefault = \Bitrix\Main\Config\Option::get('sale', 'default_currency');

        $currency = Solution::GetFrontParametrValue('CONVERT_CURRENCY') === 'Y'
            ? Solution::GetFrontParametrValue('CURRENCY_ID')
            : $currencyDefault;

        return $currency;
    }

    public static function showPriceWithSchema(array $arItem = []): string
    {
        return
            '<meta itemprop="price" content="' . static::getValue($arItem) . '">' .
            '<meta itemprop="priceCurrency" content="' . static::getCurrency() . '">';
    }
}
