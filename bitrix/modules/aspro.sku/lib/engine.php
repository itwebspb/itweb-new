<?php

namespace Aspro\Sku;

use Aspro\Sku\DTO\SkuContext;

class Engine
{
    public static function build(?array $options = null, ?array $parentOptions = null, ?array $items = null): array
    {
        $manager = SkuManager::getInstance();

        if ($options) {
            $context = SkuContext::createFromComponentParams($options);
            $manager->setContext($context);
        }

        if (!$manager->getContext()) {
            return [];
        }

        $manager->initializeServices($items);

        return $manager->getPropertyTree();
    }

    public static function buildItemsAndPropCodes(?array $options = null, ?array $parentOptions = null): array
    {
        $manager = SkuManager::getInstance();

        if ($options) {
            $context = SkuContext::createFromComponentParams($options, $parentOptions);
            $manager->setContext($context);
        }

        if (!$manager->getContext()) {
            return [];
        }

        $manager->initalizeRuleServices();

        return [
            'FILTERED_ITEMS' => $manager->getRulesItems(),
            'PROP_CODES' => $manager->getRulesProps(),
        ];
    }

    public static function refreshContextFromCache(?array $options = null, ?array $parentOptions = null, ?array $items = null, ?array $propertyCodes = null)
    {
        $manager = SkuManager::getInstance();
        if ($manager->getContext()) {
            return;
        }

        $context = SkuContext::createFromComponentParams($options, $parentOptions);
        $manager->setContext($context);
        $manager->refreshRules($items, $propertyCodes);
    }

    public static function hasAnyActiveRules(): bool
    {
        return SkuManager::hasAnyActiveRules();
    }
}
