<?php

namespace Aspro\Max\Product;

class Common
{
    public static function getElementName(array $item): string
    {
        $elementName = (!empty($item['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $item['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $item['NAME']);

        return $elementName;
    }
}
