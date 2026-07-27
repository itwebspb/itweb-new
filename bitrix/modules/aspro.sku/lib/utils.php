<?php

namespace Aspro\Sku;

class Utils
{
    public static function getComponentMarkerName(): string
    {
        return strtoupper(str_replace('.', '_', General::moduleId)).'_GROUPER';
    }

    public static function getEventConstName(): string
    {
        return self::getComponentMarkerName().'_INITED';
    }

    public static function unserialize($data, array $arOptions = [])
    {
        if (!is_string($data)) {
            return false;
        }

        $arDefaultConfig = [
            'allowed_classes' => false,
        ];
        $arConfig = array_merge($arDefaultConfig, $arOptions);

        return \unserialize($data, $arConfig);
    }
}
