<?php

namespace Aspro\Sku\Services;

use Bitrix\Iblock\Model\PropertyFeature;

class Item extends Base
{
    private array $items = [];
    private array $needPropertyValues = [];

    public function setPreparedItems(array $items = [])
    {
        foreach ($items as $item) {
            $this->items[$item['ID']] = $item;
        }

        $this->prepareItemsDetailPageUrl();
        $this->loadItemsFeatureProperties();
        $this->extractPropertyValues();
    }

    public function fillItemsAndPropsByRule()
    {
        $this->prepareItemsByRules();
        $this->prepareItemsDetailPageUrl();
        $this->loadItemsProperties();
        $this->extractPropertyValues();
    }

    private function prepareItemsByRules()
    {
        $itemIDs = Rule::getInstance()->getFilteredItemIDs();
        if (empty($itemIDs)) {
            return;
        }

        $sort = [
            $this->getContext()->sort['field'] => $this->getContext()->sort['order'],
            $this->getContext()->sort['field2'] => $this->getContext()->sort['order2'],
        ];

        $filter = [
            '=ID' => $itemIDs,
            '=ACTIVE' => 'Y',
            'IBLOCK_ID' => $this->getContext()->iblockId,
        ];

        $this->addExternalFilter($filter);

        $rsElements = \CIBlockElement::GetList($sort, $filter, false, false, ['*']);
        while ($item = $rsElements->Fetch()) {
            $this->items[$item['ID']] = $item;
        }
    }

    private function prepareItemsDetailPageUrl()
    {
        if ($this->getContext()->options['USE_MAIN_ELEMENT_SECTION'] !== 'Y') {
            $this->adjustItemsSectionId($this->getContext()->sectionId);
        }

        $this->generateDetailUrls($this->getContext()->options['SEF_URL_ELEMENT']);
    }

    private function adjustItemsSectionId(int $sectionId)
    {
        $elementsSectionsMap = $this->getElementsSectionsMap(array_keys($this->items));

        foreach ($this->items as $id => &$item) {
            if (
                isset($elementsSectionsMap[$id])
                && is_array($elementsSectionsMap[$id])
                && in_array($sectionId, $elementsSectionsMap[$id])
            ) {
                $item['IBLOCK_SECTION_ID'] = $sectionId;
            }
        }
        unset($item);
    }

    private function getElementsSectionsMap(array $itemIdList): array
    {
        $arSections = [];

        $resGroups = \CIBlockElement::GetElementGroups($itemIdList, true, ['ID', 'IBLOCK_ELEMENT_ID']);
        while ($arGroup = $resGroups->Fetch()) {
            if (!isset($arSections[$arGroup['IBLOCK_ELEMENT_ID']])) {
                $arSections[$arGroup['IBLOCK_ELEMENT_ID']] = $arGroup['ID'];
            } elseif (is_array($arSections[$arGroup['IBLOCK_ELEMENT_ID']])) {
                $arSections[$arGroup['IBLOCK_ELEMENT_ID']][] = $arGroup['ID'];
            } else {
                $arSections[$arGroup['IBLOCK_ELEMENT_ID']] = [
                    $arSections[$arGroup['IBLOCK_ELEMENT_ID']],
                    $arGroup['ID'],
                ];
            }
        }

        return $arSections;
    }

    private function generateDetailUrls(string $urlTemplate)
    {
        foreach ($this->items as &$item) {
            $item['DETAIL_PAGE_URL'] = \CIBlock::ReplaceDetailUrl($urlTemplate, $item, true, 'E');
        }
        unset($item);
    }

    private function addExternalFilter(&$filter)
    {
        if (!empty($this->getContext()->options['EXTERNAL_FILTER'])) {
            $filter = array_merge($filter, $this->getContext()->options['EXTERNAL_FILTER']);
        }
    }

    private function loadItemsProperties(): void
    {
        $propertyCodes = Rule::getInstance()->getFilteredPropertyCodes();

        $this->setItemsProperties($propertyCodes);
    }

    private function loadItemsFeatureProperties()
    {
        if (!$this->getContext()->options['USE_FEATURE_PROPS']) {
            return;
        }

        $propertyCodes = Rule::getInstance()->getFilteredPropertyCodes();
        $featureProps = PropertyFeature::getListPageShowPropertyCodes($this->getContext()->iblockId, ['CODE' => 'Y']);

        $missingFeatureProps = array_diff($propertyCodes, $featureProps);
        if ($missingFeatureProps) {
            $this->setItemsProperties($missingFeatureProps);
        }
    }

    private function setItemsProperties(array $propertyCodes = [])
    {
        if (empty($this->items) || empty($propertyCodes)) {
            return;
        }

        $itemIDs = array_keys($this->items);
        $properties = [];

        \CIBlockElement::GetPropertyValuesArray(
            $properties,
            $this->getContext()->iblockId,
            ['ID' => $itemIDs],
            ['CODE' => $propertyCodes]
        );

        foreach ($properties as $itemId => $itemProperties) {
            if (isset($this->items[$itemId])) {
                foreach ($itemProperties as &$property) {
                    if (!empty($property['VALUE'])) {
                        $displayValue = \CIBlockFormatProperties::GetDisplayValue(
                            $this->items[$itemId],
                            $property
                        );
                        $property['DISPLAY_VALUE'] = $displayValue['DISPLAY_VALUE'];
                    }
                }

                $this->items[$itemId]['DISPLAY_PROPERTIES'] = array_merge(
                    (array) $this->items[$itemId]['DISPLAY_PROPERTIES'],
                    $itemProperties
                );
            }
        }
    }

    private function extractPropertyValues(): void
    {
        foreach ($this->items as $item) {
            if (empty($item['DISPLAY_PROPERTIES'])) {
                continue;
            }

            foreach ($item['DISPLAY_PROPERTIES'] as $property) {
                $propId = $property['ID'];

                if (!isset($this->needPropertyValues[$propId])) {
                    $this->needPropertyValues[$propId] = [];
                }

                $value = $property['PROPERTY_TYPE'] === 'L'
                    ? $property['VALUE_ENUM_ID']
                    : $property['VALUE'];

                if (is_array($value)) {
                    $value = reset($value);
                }

                if (!empty($value)) {
                    $this->needPropertyValues[$propId][$value] = $value;
                }
            }
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getNeedPropertyValues(): array
    {
        return $this->needPropertyValues;
    }

    public function getSelectedItemId(): int
    {
        return $this->getContext()->elementId;
    }

    public function hasItems(): bool
    {
        return !empty($this->items);
    }
}
