<?php

namespace Aspro\Sku\Services;

use Aspro\Sku\DTO\SkuContext;
use Aspro\Sku\Orm\RulesSkuTable as RulesTable;
use Bitrix\Main\Loader;

class Rule extends Base
{
    private array $items = [];
    private array $propertyCodes = [];

    public function loadRules(): void
    {
        $select = ['FILTER_TYPE', 'FILTER_ITEMS', 'FILTER_SECTION_ID', 'FILTER_PROPERTY', 'OFFERS_PROPERTY'];
        $filter = ['ACTIVE' => 'Y', 'IBLOCK_ID' => $this->getContext()->iblockId];
        $cache = ['ttl' => RulesTable::getCacheTtl()];

        $rules = RulesTable::getList([
            'select' => $select,
            'filter' => $filter,
            'cache' => $cache,
        ])->fetchAll();

        foreach ($rules as $rule) {
            $itemIds = $this->getItemsFromRule($rule);

            if (in_array($this->getContext()->elementId, $itemIds)) {
                $this->items = array_merge($this->items, $itemIds);

                $properties = $rule['OFFERS_PROPERTY'];
                if (is_array($properties)) {
                    $this->propertyCodes = array_merge($this->propertyCodes, $properties);
                }
            }
        }

        $this->items = array_values(array_unique($this->items));
        $this->propertyCodes = array_values(array_unique($this->propertyCodes));
    }

    private function getItemsFromRule(array $rule): array
    {
        if ($rule['FILTER_TYPE'] === 'MANUAL') {
            return $this->getManualItemsByFilter($rule);
        }

        return $this->getItemsFromFilter($rule);
    }

    private function getManualItemsByFilter(array $rule): array
    {
        if (empty($rule['FILTER_ITEMS'])) {
            return [];
        }

        $filter = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $this->getContext()->iblockId,
            'ID' => $rule['FILTER_ITEMS'],
        ];

        $this->addPropertyFilter($filter, array_merge($rule, ['FILTER_PROPERTY' => []]));

        return $this->getElementIdsByFilter($filter);
    }

    private function getItemsFromFilter(array $rule): array
    {
        $filter = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $this->getContext()->iblockId,
        ];

        $this->addSectionFilter($filter, $rule);
        $this->addPropertyFilter($filter, $rule);
        $this->addCatalogFilter($filter, $rule);

        return $this->getElementIdsByFilter($filter);
    }

    private function addSectionFilter(array &$filter, array $rule): void
    {
        $sections = $rule['FILTER_SECTION_ID'] ?? [];
        if (empty($sections)) {
            return;
        }

        $filter['SECTION_ID'] = $sections;
        $filter['INCLUDE_SUBSECTIONS'] = 'Y';
    }

    private function addPropertyFilter(array &$filter, array $rule): void
    {
        $propertyCodes = $rule['FILTER_PROPERTY'] ?? [];
        $offerPropertyCodes = $rule['OFFERS_PROPERTY'] ?? [];

        if (empty($propertyCodes) && empty($offerPropertyCodes)) {
            return;
        }

        $propertyValues = [];
        \CIBlockElement::GetPropertyValuesArray(
            $propertyValues,
            $this->getContext()->iblockId,
            ['ID' => $this->getContext()->elementId],
            ['CODE' => array_merge($propertyCodes, $offerPropertyCodes)]
        );

        if ($propertyCodes) {
            foreach ($propertyCodes as $code) {
                if (isset($propertyValues[$this->getContext()->elementId][$code])) {
                    $prop = $propertyValues[$this->getContext()->elementId][$code];
                    $value = $prop['PROPERTY_TYPE'] === 'L' ? $prop['VALUE_ENUM_ID'] : $prop['VALUE'];

                    if (!empty($value)) {
                        $filter['!PROPERTY_'.$code] = false;
                        $filter['PROPERTY_'.$code] = $value;
                    }
                }
            }
        }

        if ($offerPropertyCodes) {
            foreach ($offerPropertyCodes as $code) {
                if (in_array($code, $propertyCodes)) {
                    continue;
                }

                $filter['!PROPERTY_'.$code] = false;
            }
        }
    }

    private function addCatalogFilter(&$filter): void
    {
        if (Loader::includeModule('catalog') && class_exists('CCatalogProduct')) {
            $filter['=TYPE'] = \CCatalogProduct::TYPE_PRODUCT;
        }
    }

    private function getElementIdsByFilter(array $filter): array
    {
        $ids = [];
        $rs = \CIBlockElement::GetList([], $filter, false, false, ['ID']);
        while ($item = $rs->Fetch()) {
            $ids[] = (int) $item['ID'];
        }

        return $ids;
    }

    public function getFilteredItemIDs(): array
    {
        return $this->items;
    }

    public function getFilteredPropertyCodes(): array
    {
        return $this->propertyCodes;
    }

    public function setFilterItemsIDs(array $items)
    {
        $this->items = $items;
    }

    public function setFilteredPropertyCodes(array $propertyCodes)
    {
        $this->propertyCodes = $propertyCodes;
    }

    public function hasActiveRules(): bool
    {
        return !empty($this->items) && !empty($this->propertyCodes);
    }

    public function getRulesInfo(): array
    {
        return [
            'has_rules' => $this->hasActiveRules(),
            'item_count' => count($this->items),
            'property_count' => count($this->propertyCodes),
            'items' => $this->items,
            'properties' => $this->propertyCodes,
        ];
    }

    public static function hasAnyActiveRules(): bool
    {
        return (bool) RulesTable::getRow(['select' => ['ID'], 'filter' => ['=ACTIVE' => 'Y']]);
    }
}
