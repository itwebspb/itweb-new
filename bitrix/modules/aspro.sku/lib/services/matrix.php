<?php

namespace Aspro\Sku\Services;

use Aspro\Sku\General;
use Bitrix\Main\Type\Collection;

class Matrix extends Base
{
    private array $matrix = [];
    private array $treeProps = [];
    private array $currentItem = [];
    private array $itemsWithTree = [];

    private const PROP_PREFIX = 'PROP_';

    public function buildMatrix(): void
    {
        $items = Item::getInstance()->getItems();
        $properties = Property::getInstance()->getProperties();
        $stringTable = Property::getInstance()->getStringTable();

        if (empty($items) || empty($properties)) {
            return;
        }

        $this->itemsWithTree = $this->buildItemsTree($items);
        $this->buildMatrixData($this->itemsWithTree, $properties, $stringTable);
        $this->buildTreeProps($properties);
        $this->setActivePropsValue();
        $this->clearHiddenProps();
        $this->setItemPropsToTreeProps();
    }

    private function buildItemsTree(array $items): array
    {
        $properties = Property::getInstance()->getProperties();
        $stringTable = Property::getInstance()->getStringTable();
        $itemsWithTree = [];

        foreach ($items as $itemId => $item) {
            $itemsWithTree[$itemId] = $item;
            $itemsWithTree[$itemId]['TREE'] = [];

            foreach ($properties as $code => $property) {
                $treeKey = self::PROP_PREFIX.$property['ID'];

                if (isset($item['DISPLAY_PROPERTIES'][$code])) {
                    $propValue = $item['DISPLAY_PROPERTIES'][$code];
                    $value = $this->getPropertyValue($property, $propValue, $stringTable);
                    $itemsWithTree[$itemId]['TREE'][$treeKey] = $value;
                } else {
                    $itemsWithTree[$itemId]['TREE'][$treeKey] = 0;
                }
            }
        }

        return $itemsWithTree;
    }

    private function buildMatrixData(array $items, array $properties, array $stringTable): void
    {
        $matrix = [];
        $matrixFields = array_fill_keys(array_keys($properties), false);

        foreach ($items as $itemId => $item) {
            $row = [];
            foreach ($properties as $code => $property) {
                $cell = [
                    'VALUE' => 0,
                    'SORT' => PHP_INT_MAX,
                    'NA' => true,
                ];

                if (isset($item['DISPLAY_PROPERTIES'][$code])) {
                    $matrixFields[$code] = true;
                    $cell['NA'] = false;

                    $propValue = $item['DISPLAY_PROPERTIES'][$code];
                    $value = $this->getPropertyValue($property, $propValue, $stringTable);

                    $cell['VALUE'] = $value;
                    $cell['SORT'] = $properties[$code]['VALUES'][$value]['SORT'] ?? PHP_INT_MAX;
                }

                $row[$code] = $cell;
            }

            $matrix[$itemId] = $row;
        }

        $this->matrix = [
            'ITEMS' => $matrix,
            'FIELDS' => $matrixFields,
        ];
    }

    private function getPropertyValue(array $property, array $propValue, array $stringTable): int
    {
        if (empty($propValue['VALUE'])) {
            return 0;
        }

        try {
            if ($property['USER_TYPE'] == 'directory') {
                return $property['XML_MAP'][$propValue['VALUE']] ?? 0;
            } elseif ($property['PROPERTY_TYPE'] == 'S') {
                return $stringTable[$property['CODE']][$propValue['VALUE']] ?? 0;
            } elseif ($property['PROPERTY_TYPE'] == 'L') {
                return (int) ($propValue['VALUE_ENUM_ID'] ?? 0);
            } elseif ($property['PROPERTY_TYPE'] == 'E') {
                return (int) ($propValue['VALUE'] ?? 0);
            }
        } catch (\Throwable $e) {
        }

        return 0;
    }

    private function buildTreeProps(array $properties): void
    {
        $treeProps = [];
        $matrix = $this->matrix['ITEMS'] ?? [];
        $matrixFields = $this->matrix['FIELDS'] ?? [];

        foreach ($properties as $code => $property) {
            if (empty($matrixFields[$code])) {
                continue;
            }

            $propValues = [];
            foreach ($matrix as $itemId => $row) {
                if (isset($row[$code]) && !$row[$code]['NA']) {
                    $valueId = $row[$code]['VALUE'];
                    if (isset($property['VALUES'][$valueId])) {
                        $propValues[$valueId] = $property['VALUES'][$valueId];
                    }
                }
            }

            if (!empty($propValues)) {
                Collection::sortByColumn($propValues, ['SORT' => SORT_ASC, 'NAME' => SORT_ASC]);

                $treeProps[$code] = [
                    'ID' => $property['ID'],
                    'CODE' => $property['CODE'],
                    'NAME' => $property['NAME'],
                    'SORT' => $property['SORT'],
                    'HINT' => $property['HINT'],
                    'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
                    'USER_TYPE' => $property['USER_TYPE'],
                    'LINK_IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
                    'SHOW_MODE' => $property['SHOW_MODE'],
                    'SHOW_PREVIEW_PICTURE' => $property['SHOW_PREVIEW_PICTURE'] ?? false,
                    'VALUES' => $propValues,
                ];
            }
        }

        $this->treeProps = $treeProps;
    }

    private function setActivePropsValue(): void
    {
        $items = $this->itemsWithTree;
        $selectedItemId = Item::getInstance()->getSelectedItemId();

        if (isset($items[$selectedItemId])) {
            $this->currentItem = $items[$selectedItemId];
        } else {
            $this->currentItem = reset($items) ?: [];
        }

        if (empty($this->currentItem)) {
            return;
        }

        $filter = [];
        foreach ($this->treeProps as $code => &$property) {
            $treeKey = self::PROP_PREFIX.$property['ID'];
            $availableValues = $this->getAvailableValues($filter, $treeKey, $items);

            if (empty($availableValues)) {
                continue;
            }

            $currentValue = $this->currentItem['TREE'][$treeKey] ?? null;

            if (in_array($currentValue, $availableValues)) {
                $filter[$treeKey] = $currentValue;
            } else {
                $filter[$treeKey] = $availableValues[0];
            }

            $purchasableValues = $this->getPurchasableValues($filter, $treeKey, $availableValues, $items);
            $this->updatePropertyValues($filter[$treeKey], $availableValues, $property, $purchasableValues);
        }
    }

    private function getAvailableValues(array $filter, string $treeKey, array $items): array
    {
        $values = [];

        foreach ($items as $item) {
            if (!isset($item['TREE']) || !isset($item['TREE'][$treeKey])) {
                continue;
            }

            $matches = true;
            foreach ($filter as $key => $filterValue) {
                if (!isset($item['TREE'][$key]) || $item['TREE'][$key] != $filterValue) {
                    $matches = false;
                    break;
                }
            }

            if ($matches) {
                $value = $item['TREE'][$treeKey];
                if (!in_array($value, $values)) {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    private function getPurchasableValues(array $filter, string $treeKey, array $availableValues, array $items): array
    {
        $purchasable = [];

        foreach ($availableValues as $value) {
            $testFilter = $filter;
            $testFilter[$treeKey] = $value;

            if ($this->canBuy($testFilter, $items)) {
                $purchasable[] = $value;
            }
        }

        return $purchasable;
    }

    private function updatePropertyValues($selectedValue, array $availableValues, array &$property, array $purchasableValues): void
    {
        $showCount = 0;
        $currentValueName = '';

        foreach ($property['VALUES'] as &$value) {
            $isCurrent = ($value['ID'] === $selectedValue);
            $isAvailable = in_array($value['ID'], $availableValues);
            $isPurchasable = empty($purchasableValues) || in_array($value['ID'], $purchasableValues);

            $value['ACTIVE'] = $isCurrent;
            $value['MISSING'] = !$isPurchasable;
            $value['STYLE'] = $isAvailable ? '' : 'display: none;';
            $value['VISIBLE'] = $isAvailable ? 'Y' : 'N';

            if ($isAvailable && $value['ID'] != 0) {
                ++$showCount;
            }

            if ($isCurrent) {
                $currentValueName = $value['NAME'];
                $property['VALUE'] = $currentValueName;
            }

            if ($property['SHOW_PREVIEW_PICTURE']) {
                $this->setPropertyImageFromSku($value, $property);
            }
        }

        $property['STYLE'] = $showCount > 0 ? '' : 'display: none;';
        $property['VISIBLE'] = $showCount > 0 ? 'Y' : 'N';
    }

    private function setPropertyImageFromSku(array &$value, array $property): void
    {
        $items = $this->itemsWithTree;
        $treeKey = self::PROP_PREFIX.$property['ID'];

        foreach ($items as $item) {
            if (isset($item['TREE'][$treeKey]) && $item['TREE'][$treeKey] == $value['ID']) {
                $picture = $item['PREVIEW_PICTURE_FIELD'] ?? $item['PREVIEW_PICTURE'] ?? null;
                if ($picture) {
                    if (!is_array($picture)) {
                        $picture = \CFile::GetFileArray($picture);
                    }
                    $value['PICT'] = $picture;
                    break;
                }
            }
        }
    }

    private function clearHiddenProps(): void
    {
        $this->treeProps = array_filter($this->treeProps, function ($prop) {
            return ($prop['VISIBLE'] ?? '') === 'Y';
        });
    }

    private function setItemPropsToTreeProps(): void
    {
        $items = $this->itemsWithTree;

        // set can_buy
        if ($this->getContext()->options['USE_AVAILABILITY']) {
            foreach (GetModuleEvents(General::moduleId, 'OnAsproSkuSetCanBuy', true) as $arEvent) {
                ExecuteModuleEventEx(
                    $arEvent,
                    [
                        &$items,
                        array_merge(
                            $this->getContext()->parentComponentParams,
                            [
                                'USE_REGION' => $this->getContext()->useRegion,
                                'STORES' => $this->getContext()->stores,
                            ]
                        ),
                    ]
                );
            }
        }

        $propertyOrder = array_keys($this->treeProps);
        $propertyIds = array_column($this->treeProps, 'ID');

        foreach ($propertyOrder as $propIndex => $propCode) {
            $propId = $this->treeProps[$propCode]['ID'];

            $baseFilter = $this->buildBaseFilter($propIndex, $propertyIds);

            foreach ($this->treeProps[$propCode]['VALUES'] as &$value) {
                if (($value['VISIBLE'] ?? 'N') !== 'Y' || ($value['MISSING'] ?? false)) {
                    $value['DETAIL_PAGE_URL'] = '';
                    continue;
                }

                $currentFilter = $baseFilter;
                $currentFilter[self::PROP_PREFIX.$propId] = $value['ID'];

                $suitableItem = $this->findSuitableItemForLink($currentFilter, $items, $propertyIds, $propId);

                $value['DETAIL_PAGE_URL'] = $suitableItem ? $suitableItem['DETAIL_PAGE_URL'] : '';

                if (array_key_exists('CAN_BUY', $suitableItem)) {
                    $value['CAN_BUY'] = (bool) $suitableItem['CAN_BUY'];
                }
            }
            unset($value);
        }
    }

    private function findSuitableItemForLink(array $currentFilter, array $items, array $propertyIds, int $currentPropId): ?array
    {
        $fullFilter = $this->buildFullFilterForLink($currentFilter, $propertyIds, $currentPropId);
        $item = $this->findItemByFilter($fullFilter, $items);
        if ($item) {
            return $item;
        }

        $item = $this->findItemByFilter($currentFilter, $items);
        if ($item) {
            return $item;
        }

        $fallbackFilter = [self::PROP_PREFIX.$currentPropId => $currentFilter[self::PROP_PREFIX.$currentPropId]];

        return $this->findItemByFilter($fallbackFilter, $items);
    }

    private function buildFullFilterForLink(array $currentFilter, array $propertyIds, int $currentPropId): array
    {
        $fullFilter = $currentFilter;

        foreach ($propertyIds as $propId) {
            $propKey = self::PROP_PREFIX.$propId;

            if ($propId === $currentPropId) {
                continue;
            }

            if (!isset($fullFilter[$propKey]) && isset($this->currentItem['TREE'][$propKey])) {
                $fullFilter[$propKey] = $this->currentItem['TREE'][$propKey];
            }
        }

        return $fullFilter;
    }

    private function buildBaseFilter(int $currentPropIndex, array $propertyIds): array
    {
        $filter = [];
        for ($i = 0; $i < $currentPropIndex; ++$i) {
            $propId = $propertyIds[$i];
            $treeKey = self::PROP_PREFIX.$propId;
            if (isset($this->currentItem['TREE'][$treeKey])) {
                $filter[$treeKey] = $this->currentItem['TREE'][$treeKey];
            }
        }

        return $filter;
    }

    private function findSuitableItem(array $filter, array $items): ?array
    {
        $item = $this->findItemByFilter($filter, $items);
        if ($item) {
            return $item;
        }

        return $this->findItemByFilter($filter, $items, false);
    }

    private function findItemByFilter(array $filter, array $items, bool $exact = true): ?array
    {
        foreach ($items as $item) {
            $matches = true;
            foreach ($filter as $key => $value) {
                if (!isset($item['TREE'][$key]) || $item['TREE'][$key] != $value) {
                    $matches = false;
                    if ($exact) {
                        break;
                    }
                }
            }

            if ($matches) {
                return $item;
            }
        }

        return null;
    }

    private function canBuy(array $filter, array $items): bool
    {
        return $this->findItemByFilter($filter, $items) !== null;
    }

    public function getTreeProps(): array
    {
        return $this->treeProps;
    }

    public function getMatrix(): array
    {
        return $this->matrix;
    }

    public function hasMatrix(): bool
    {
        return !empty($this->matrix) && !empty($this->treeProps);
    }

    public function canBuyCombination(array $properties): bool
    {
        $filter = [];
        foreach ($properties as $propCode => $valueId) {
            $prop = $this->findPropertyByCode($propCode);
            if ($prop) {
                $filter[self::PROP_PREFIX.$prop['ID']] = $valueId;
            }
        }

        return $this->canBuy($filter, $this->itemsWithTree);
    }

    public function findItemByProperties(array $properties): ?array
    {
        $filter = [];
        foreach ($properties as $propCode => $valueId) {
            $prop = $this->findPropertyByCode($propCode);
            if ($prop) {
                $filter[self::PROP_PREFIX.$prop['ID']] = $valueId;
            }
        }

        return $this->findItemByFilter($filter, $this->itemsWithTree);
    }

    private function findPropertyByCode(string $code): ?array
    {
        $properties = Property::getInstance()->getProperties();

        return $properties[$code] ?? null;
    }
}
