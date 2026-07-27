<?php

namespace Aspro\Max\Product;

use Aspro\Max\Product\Price;
use Bitrix\Catalog\VatTable;
use Bitrix\Main\Localization\Loc;
use CMax as Solution;

class Vat
{
    public static function getRate($productId): int
    {
        $item = \CCatalogProduct::GetVATInfo($productId)->fetch();
        if (empty($item)) {
            return 0;
        }

        return (int) $item['RATE'];
    }

    public static function isInfoEnabled(): bool
    {
        return Solution::GetFrontParametrValue('DISPLAY_VAT_INFO') === 'Y';
    }

    private static function getMessage(array $item, $arParams): string
    {
        $rate = static::getRate($item['ID']);

        if ($rate <= 0) {
            return Loc::getMessage('VAT_NOT_INCLUDED');
        }

        if ($arParams['PRICE_VAT_INCLUDE'] == 'Y') {
            return Loc::getMessage('VAT_INCLUDED', ['#VAT_RATE#' => $rate]);
        }

        return Loc::getMessage('VAT_DIFF_WARNING', ['#VAT_RATE#' => $rate]);
    }

    public static function show(array $item, array $arParams): string
    {
        if(!static::isInfoEnabled()) {
            return '';
        }

       $text = Price::getValue($item) > 0 ? static::getMessage($item, $arParams) : '';

       return '<div class="vat_text muted777 font_xs ">' . $text . '</div>';
    }
}
