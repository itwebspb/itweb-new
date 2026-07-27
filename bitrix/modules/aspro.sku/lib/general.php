<?php

namespace Aspro\Sku;

use Bitrix\Main\Localization\Loc;

class General
{
    public const moduleId = 'aspro.sku';
    public const partnerName = 'aspro';
    public const DATA_DIR_NAME = 'data';
    public const langPrefix = 'ASPRO_SKU__';
    public const assetsPath = 'aspro/sku';

    public static function getDocRoot(): string
    {
        return rtrim(realpath(__DIR__.'/../../../../'), '/\\');
    }

    public static function getModuleRoot(): string
    {
        return rtrim(realpath(__DIR__.'/../'), '/\\');
    }

    public static function getCurrentDir(string $path): string
    {
        return rtrim(realpath($path), '/\\');
    }

    public static function getRelativePath(string $absPath): string
    {
        return str_replace(static::getDocRoot(), '', $absPath);
    }

    public static function getExtensionName(string $name): string
    {
        return implode('.', [self::moduleId, $name]);
    }

    public static function getMessage($code, $replace = null, $language = null): ?string
    {
        return Loc::getMessage(self::langPrefix.$code, $replace, $language);
    }
}
