<?php

namespace Aspro\Sku\Admin\Controller;

use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\IBlock\IblockSiteTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\TypeLanguageTable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;

class Iblock extends \Bitrix\Main\Engine\Controller
{
    protected static array $iblocks = [];
    protected static array $sections = [];
    protected static array $properties = [];
    protected static array $offerCatalogIBlocks = [];
    protected static array $siteIBlocks = [];

    public function configureActions()
    {
        return [
            'getType' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                ],
            ],
            'getIBlocks' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                ],
            ],
            'getSections' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                ],
            ],
            'getIBlockProperties' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function getTypesAction(): array
    {
        return static::getTypes();
    }

    public function getIBlocksAction(string $siteId, string $lang): array
    {
        return static::getIBlocks($siteId, $lang);
    }

    public function getSectionsAction(int $iblockId): array
    {
        $sections = static::getSections($iblockId);
        return [
            'ORDER' => array_keys($sections),
            'SECTIONS' => $sections
        ];
    }

    public function getIBlockPropertiesAction(int $iblockId): array
    {
        return static::getIBlockProperties($iblockId);
    }

    public static function getSites(): array
    {
        $allowedKeys = ['NAME', 'ID', 'LID'];

        $sites = SiteTable::getList([
            'select' => $allowedKeys,
            'filter' => ['=ACTIVE' => 'Y'],
            'order' => ['SORT' => 'DESC'],
        ])->fetchAll();

        $listSites = [];
        foreach ($sites as $site) {
            $listSites[$site['ID']] = '['.$site['LID'].'] '.$site['NAME'];
        }

        return $listSites;
    }

    public static function getIBlocks(string $siteId, string $lang = LANGUAGE_ID): array
    {
        $key = md5($siteId);
        if (!isset(static::$iblocks[$key])) {
            $filter = static::getIBlockFilter($siteId);
            if (empty($filter)) {
                return [];
            }

            $iblockList = IblockTable::getList([
                'select' => ['ID', 'NAME', 'IBLOCK_TYPE_ID'],
                'filter' => array_merge(['=ACTIVE' => 'Y'], $filter),
                'order' => ['SORT' => 'ASC'],
            ])->fetchAll();

            $listIBlocks = [];
            foreach ($iblockList as $arIBlock) {
                $iblockTypeId = $arIBlock['IBLOCK_TYPE_ID'];

                if (!isset($listIBlocks[$iblockTypeId])) {
                    $arIBlockType = TypeLanguageTable::getList([
                        'select' => ['NAME'],
                        'filter' => [
                            '=IBLOCK_TYPE_ID' => $iblockTypeId,
                            '=LANGUAGE_ID' => $lang,
                        ],
                    ])->fetch();

                    $listIBlocks[$iblockTypeId] = [
                        'TITLE' => $arIBlockType['NAME'] ?? $iblockTypeId,
                        'ITEMS' => [],
                    ];
                }

                $listIBlocks[$iblockTypeId]['ITEMS'][$arIBlock['ID']] = '['.$arIBlock['ID'].'] '.$arIBlock['NAME'];
            }

            static::$iblocks[$key] = $listIBlocks;
        }

        return static::$iblocks[$key];
    }

    protected static function getIBlockFilter(string $siteId): array
    {
        $iblocksBySiteId = static::getIBlocksBySiteId($siteId);
        if (empty($iblocksBySiteId)) {
            return [
                '=LID' => $siteId
            ];
        }

        $filter = ['ID' => $iblocksBySiteId];
        static::filterIBlocksByCatalogModule($filter);

        return $filter;
    }

    protected static function getIBlocksBySiteId(string $siteId)
    {
        if (!static::$siteIBlocks[$siteId]) {
            $iblockListBySite = IBlockSiteTable::getList([
                'select' => ['IBLOCK_ID'],
                'filter' => [
                    'SITE_ID' => $siteId
                ],
            ])->fetchAll();
            if ($iblockListBySite) {
                static::$siteIBlocks[$siteId] = array_column($iblockListBySite, 'IBLOCK_ID');
            }
        }

        return static::$siteIBlocks[$siteId];
    }

    protected static function filterIBlocksByCatalogModule(array &$filter)
    {
        if (Loader::includeModule('catalog')) {
            if (!self::$offerCatalogIBlocks) {
                $catalogIBlocks = CatalogIblockTable::getList([
                    'select' => ['IBLOCK_ID'],
                    'filter' => ['PRODUCT_IBLOCK_ID' => 0],
                ])->fetchAll();

                if ($catalogIBlocks) {
                    self::$offerCatalogIBlocks = array_column($catalogIBlocks, 'IBLOCK_ID');
                }
            }

            if (self::$offerCatalogIBlocks) {
                $filter['ID'] = $filter['ID']
                    ? array_intersect($filter['ID'], self::$offerCatalogIBlocks)
                    : self::$offerCatalogIBlocks;
            }
        }
    }

    public static function getSections(int $iblockId): array
    {
        if (!isset(static::$sections[$iblockId])) {
            static::$sections[$iblockId] = array_map(
                fn ($section) => str_repeat(' . ', (int) $section['DEPTH_LEVEL']).$section['NAME'],
                static::getRowsByTreeSections(static::getTreeSections($iblockId))
            );
        }

        return static::$sections[$iblockId];
    }

    protected static function getTreeSections($iblockId, $depthLevel = 1, $parentId = 0)
    {
        $sections = SectionTable::getList([
            'select' => ['ID', 'NAME', 'PARENT_ID' => 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL'],
            'order' => ['DEPTH_LEVEL' => 'ASC', 'SORT' => 'DESC', 'NAME' => 'ASC'],
            'filter' => [
                '=ACTIVE' => 'Y',
                '=IBLOCK_ID' => $iblockId,
                '>=DEPTH_LEVEL' => $depthLevel,
            ],
        ])->fetchAll();

        $link = [];
        $link[$parentId] = &$treeSections;

        foreach ($sections as $section) {
            $link[intval($section['PARENT_ID'])]['CHILD'][$section['ID']] = $section;
            $link[$section['ID']] = &$link[intval($section['PARENT_ID'])]['CHILD'][$section['ID']];
        }

        return $treeSections['CHILD'];
    }

    protected static function getRowsByTreeSections($treeSections)
    {
        if (!$treeSections) {
            return [];
        }

        $result = [];

        foreach ($treeSections as $treeSection) {
            $_treeSection = $treeSection;
            unset($_treeSection['CHILD']);
            $result[$_treeSection['ID']] = $_treeSection;

            if ($treeSection['CHILD']) {
                foreach (static::getRowsByTreeSections($treeSection['CHILD']) as $section) {
                    $result[$section['ID']] = $section;
                }
            }
        }

        return $result;
    }

    public static function getIBlockProperties(int $iblockId): array
    {
        if (!isset(static::$properties[$iblockId])) {
            static::$properties[$iblockId] = [];

            $properties = PropertyTable::getList([
                'select' => ['ID', 'CODE', 'NAME', 'PROPERTY_TYPE', 'MULTIPLE'],
                'filter' => [
                    '=ACTIVE' => 'Y',
                    '=IBLOCK_ID' => $iblockId,
                    '=MULTIPLE' => 'N',
                    '=PROPERTY_TYPE' => [
                        PropertyTable::TYPE_LIST,
                        PropertyTable::TYPE_ELEMENT,
                        PropertyTable::TYPE_STRING,
                    ],
                ],
                'order' => ['SORT' => 'ASC'],
            ])->fetchAll();
            foreach ($properties as $property) {
                static::$properties[$iblockId][$property['CODE']] = '['.$property['ID'].'] '.$property['NAME'].' ('.$property['CODE'].')';
            }
        }

        return static::$properties[$iblockId];
    }

    public static function getLinkedItems(int $iblockId, array $items = []): array
    {
        if (empty($items)) {
            return [];
        }

        $result = array_flip($items);
        $elements = \Bitrix\Iblock\ElementTable::getList([
            'select' => ['ID', 'NAME'],
            'filter' => [
                'ID' => $items,
            ],
        ])->fetchAll();

        foreach ($elements as $values) {
            $result[$values['ID']] = $values['NAME'];
        }

        return $result;
    }
}
