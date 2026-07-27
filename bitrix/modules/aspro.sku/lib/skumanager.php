<?php

namespace Aspro\Sku;

class SkuManager
{
    use Traits\Singletonable;
    use Traits\SkuContextable;

    public function initializeServices(?array $items = null): void
    {
        if (!$this->getContext()) {
            return;
        }

        $this->prepareRules();
        if (!Services\Rule::getInstance()->hasActiveRules()) {
            return;
        }

        $this->prepareItems($items);
        $this->prepareProperties();
        $this->prepareMatrix();
    }

    public function initalizeRuleServices(): void
    {
        $this->prepareRules();
    }

    public function refreshRules(array $items, array $propertyCodes)
    {
        $ruleService = Services\Rule::getInstance();

        $ruleService->setContext($this->getContext());
        $ruleService->setFilterItemsIDs($items);
        $ruleService->setFilteredPropertyCodes($propertyCodes);
    }

    private function prepareRules()
    {
        $ruleService = Services\Rule::getInstance();

        $ruleService->setContext($this->getContext());
        if (!$ruleService->hasActiveRules()) {
            $ruleService->loadRules();
        }
    }

    private function prepareItems(?array $items = null)
    {
        $itemService = Services\Item::getInstance();

        $itemService->setContext($this->getContext());
        if ($items) {
            $itemService->setPreparedItems($items);
        } else {
            $itemService->fillItemsAndPropsByRule();
        }
    }

    private function prepareProperties()
    {
        $propertyService = Services\Property::getInstance();

        $propertyService->setContext($this->getContext());
        $propertyService->loadProperties();
    }

    private function prepareMatrix()
    {
        $matrixService = Services\Matrix::getInstance();

        $matrixService->setContext($this->getContext());
        $matrixService->buildMatrix();
    }

    public function getPropertyTree(): array
    {
        if (!$this->hasActiveRules()) {
            return [];
        }

        return Services\Matrix::getInstance()->getTreeProps();
    }

    public function getRulesItems(): array
    {
        if (!$this->hasActiveRules()) {
            return [];
        }

        return Services\Rule::getInstance()->getFilteredItemIDs();
    }

    public function getRulesProps(): array
    {
        if (!$this->hasActiveRules()) {
            return [];
        }

        return Services\Rule::getInstance()->getFilteredPropertyCodes();
    }

    public function hasActiveRules(): bool
    {
        return Services\Rule::getInstance()->hasActiveRules();
    }

    public static function hasAnyActiveRules(): bool
    {
        return Services\Rule::hasAnyActiveRules();
    }
}
