<?php

namespace Aspro\Sku;

use Bitrix\Main\Config\Option;

class Config
{
    public static function getEnabled(string $siteId = ''): bool
    {
        return Option::get(General::moduleId, 'ENABLED', 'Y', self::getSiteId($siteId)) !== 'N';
    }

    public static function useAvailability(string $siteId = ''): bool
    {
        return Option::get(General::moduleId, 'USE_AVAILABILITY', 'N', self::getSiteId($siteId)) !== 'N';
    }

    public static function getSiteId(string $siteId = ''): string
    {
        return $siteId ?: SITE_ID;
    }

    public static function getCacheTable(string $siteId = ''): string
    {
        return Option::get(General::moduleId, 'CACHE_TABLE', '0', self::getSiteId($siteId)) ?: '86400';
    }
}
