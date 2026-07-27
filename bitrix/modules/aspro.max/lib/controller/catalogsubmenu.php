<?php

namespace Aspro\Max\Controller;

use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Data\TaggedCache;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use CMax as Solution;
use CMaxCache as SolutionCache;

class CatalogSubmenu extends Controller
{
    private const CACHE_TTL = 86400;
    private const CACHE_PATH = '/aspro/max/catalogsubmenu/';

    private static array $urlTemplateCache = [];

    public function configureActions(): array
    {
        return [
            'getItems' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function getItemsAction(int $sectionId): array
    {
        if ($sectionId <= 0) {
            return ['items' => []];
        }

        if (!Loader::includeModule('iblock')) {
            return ['items' => []];
        }

        $iblockId = (int) Solution::getFrontParametrValue('CATALOG_IBLOCK_ID');
        if (!$iblockId) {
            return ['items' => []];
        }

        $cache = Cache::createInstance();
        $cacheId = 'section_'.$sectionId.'_iblock_'.$iblockId;

        if ($cache->initCache(self::CACHE_TTL, $cacheId, self::CACHE_PATH)) {
            return $cache->getVars();
        }

        $result = $this->buildItems($sectionId, $iblockId);

        $taggedCache = new TaggedCache();
        $taggedCache->startTagCache(self::CACHE_PATH);
        $taggedCache->registerTag(SolutionCache::GetIBlockCacheTag($iblockId));
        $taggedCache->endTagCache();

        $cache->startDataCache();
        $cache->endDataCache($result);

        return $result;
    }

    private function buildItems(int $sectionId, int $iblockId): array
    {
        $l1row = SectionTable::getList([
            'filter' => ['=ID' => $sectionId, '=IBLOCK_ID' => $iblockId],
            'select' => ['LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL'],
        ])->fetch();

        if (!$l1row) {
            return ['items' => []];
        }

        $l1Depth = (int) $l1row['DEPTH_LEVEL'];
        $maxDepthMenu = (int) Solution::getFrontParametrValue('MAX_DEPTH_MENU') ?: 4;
        $maxDepth = min($l1Depth + 2, $maxDepthMenu - 1);

        if ($maxDepth <= $l1Depth) {
            return ['items' => []];
        }

        $urlTemplate = $this->getUrlTemplate($iblockId);

        $rs = SectionTable::getList([
            'order' => ['DEPTH_LEVEL' => 'ASC', 'SORT' => 'ASC'],
            'filter' => [
                '=IBLOCK_ID' => $iblockId,
                '=ACTIVE' => 'Y',
                '=GLOBAL_ACTIVE' => 'Y',
                '>LEFT_MARGIN' => $l1row['LEFT_MARGIN'],
                '<RIGHT_MARGIN' => $l1row['RIGHT_MARGIN'],
                '<=DEPTH_LEVEL' => $maxDepth,
            ],
            'select' => ['ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL'],
        ]);

        $sections = [];
        while ($row = $rs->fetch()) {
            $sections[(int) $row['ID']] = $row;
        }

        $l2items = [];
        foreach ($sections as $id => $section) {
            $depth = (int) $section['DEPTH_LEVEL'];
            if ($depth === $l1Depth + 1) {
                $l2items[$id] = [
                    'id' => $id,
                    'name' => $section['NAME'],
                    'url' => \CIBlock::ReplaceDetailUrl($urlTemplate, [
                        'IBLOCK_ID' => $iblockId,
                        'ID' => $id,
                        'CODE' => $section['CODE'],
                        'LANG_DIR' => SITE_DIR,
                    ], false, 'S'),
                    'children' => [],
                ];
            } elseif ($depth === $l1Depth + 2) {
                $parentId = (int) $section['IBLOCK_SECTION_ID'];
                if (!isset($l2items[$parentId])) {
                    continue;
                }

                $l2items[$parentId]['children'][] = [
                    'name' => $section['NAME'],
                    'url' => \CIBlock::ReplaceDetailUrl($urlTemplate, [
                        'IBLOCK_ID' => $iblockId,
                        'ID' => $id,
                        'CODE' => $section['CODE'],
                        'LANG_DIR' => SITE_DIR,
                    ], false, 'S'),
                ];
            }
        }

        return ['items' => array_values($l2items)];
    }

    private function getUrlTemplate(int $iblockId): string
    {
        if (isset(self::$urlTemplateCache[$iblockId])) {
            return self::$urlTemplateCache[$iblockId];
        }

        $template = '';
        $rs = \CIBlock::GetByID($iblockId);
        if ($ar = $rs->Fetch()) {
            $template = (string) $ar['SECTION_PAGE_URL'];
        }

        self::$urlTemplateCache[$iblockId] = $template;

        return $template;
    }
}
