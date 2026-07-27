<?php

namespace Aspro\Sku\Services;

use Aspro\Sku\DTO\SkuContext;
use Bitrix\Iblock\ElementTable;

class ItemService
{
    private SkuContext $context;
    private RuleService $ruleService;
    private array $items = [];
    private array $needPropertyValues = [];

    public function __construct(SkuContext $context, RuleService $ruleService)
    {
        $this->context = $context;
        $this->ruleService = $ruleService;
        $this->loadItems();
        $this->extractPropertyValues();
    }

    private function loadItems(): void
    {
        $itemIDs = $this->ruleService->getFilteredItemIDs();
        if (empty($itemIDs)) {
            return;
        }

        $items = ElementTable::getList([
            'select' => ['*'],
            'filter' => [
                '=ID' => $itemIDs,
                '=ACTIVE' => 'Y',
                '=IBLOCK_ID' => $this->context->iblockId
            ],
            'cache' => ['ttl' => 3600]
        ])->fetchAll();

        foreach ($items as $item) {
            $item['DETAIL_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
                $this->context->options['SEF_URL_ELEMENT'],
                $item,
                true,
                'E'
            );
            $this->items[$item['ID']] = $item;
        }

        $this->loadItemProperties();
    }

    private function loadItemProperties(): void
    {
        $propertyCodes = $this->ruleService->getFilteredPropertyCodes();
        if (empty($this->items) || empty($propertyCodes)) {
            return;
        }

        $itemIDs = array_keys($this->items);
        $properties = [];

        \CIBlockElement::GetPropertyValuesArray(
            $properties,
            $this->context->iblockId,
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
                $this->items[$itemId]['DISPLAY_PROPERTIES'] = $itemProperties;
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
        return $this->context->elementId;
    }

    public function hasItems(): bool
    {
        return !empty($this->items);
    }
}
