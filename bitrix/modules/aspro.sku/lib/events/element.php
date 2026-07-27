<?php

namespace Aspro\Sku\Events;

use Aspro\Sku\ORM\RulesSkuTable;
use Bitrix\Iblock\IblockSiteTable;

class Element
{
    public static function onAfterIBlockElementAddHandler(&$arFields)
    {
        if ((int) $arFields['ID']) {
            RulesSkuTable::clearComponentCache(static::getSiteListByIblockId((int) $arFields['IBLOCK_ID']));
        }
    }

    public static function onAfterIblockElementUpdateHandler(&$arFields)
    {
        if ($arFields['RESULT']) {
            RulesSkuTable::clearComponentCache(static::getSiteListByIblockId((int) $arFields['IBLOCK_ID']));
        }
    }

    public static function onAfterIBlockElementDeleteHandler($arFields)
    {
        RulesSkuTable::clearComponentCache(static::getSiteListByIblockId((int) $arFields['IBLOCK_ID']));
    }

    protected static function getSiteListByIblockId(int $iblockId): array
    {
        $siteIdList = IblockSiteTable::getList([
            'select' => ['SITE_ID'],
            'filter' => ['IBLOCK_ID' => $iblockId]
        ])->fetchAll();

        return empty($siteIdList) ? [] : array_column($siteIdList, 'SITE_ID');
    }
}
