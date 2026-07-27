<?php

namespace Aspro\Sku\Enums;

use Aspro\Sku\General;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

enum RulesSkuFilterType
{
    case MANUAL;
    case FILTER;

    public function getMessage(): string
    {
        return match ($this) {
            self::MANUAL => General::getMessage('RULES_SKU_FILTER_TYPE_'.$this->getName()),
            self::FILTER => General::getMessage('RULES_SKU_FILTER_TYPE_'.$this->getName()),
        };
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameLowerCase(): string
    {
        return strtolower($this->name);
    }

    public static function getNames(): array
    {
        return array_map(fn ($key) => $key->getName(), self::cases());
    }

    public static function getNamesWithLang(): array
    {
        $filterTypes = [];

        foreach (self::cases() as $filterType) {
            $filterTypes[$filterType->getName()] = $filterType->getMessage();
        }

        return $filterTypes;
    }
}
