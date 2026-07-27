<?php

namespace Aspro\Max\Scheme\List;

use Aspro\Max\Utils as SolutionUtils;
use Bitrix\Main\Application;

abstract class Content
{
    protected array $arParams;
    protected array $arItemFilter;

    protected string $siteName = '';
    protected string $siteUrl = '';
    protected string $currentUrl = '';

    abstract public function buildSchema(): array;

    public function __construct(array $arParams, array $arItemFilter)
    {
        $this->arParams = $arParams;
        $this->arItemFilter = $arItemFilter;
        $this->prepare();
    }

    protected function prepare(): void
    {
        $this->siteName = SolutionUtils::getSiteInfo()['NAME'] ?? '';
        $this->siteUrl = SolutionUtils::getSiteURL();
        $this->currentUrl = SolutionUtils::getCurrentUrl();
    }

    public function show(): void
    {
        $arSchema = $this->buildSchema();
        if (!empty($arSchema)) {
            ?>
            <script type="application/ld+json">
                <?= str_replace("'", '"', \CUtil::PhpToJSObject($arSchema, false, true)); ?>
            </script>
            <?php
        }
    }

    protected function prepareElements(array $arSelect = []): array
    {
        $arDefaultSelect = [
            'ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_PAGE_URL',
            'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'DATE_CREATE', 'IBLOCK_SECTION_ID',
        ];

        $arSelect = array_merge($arDefaultSelect, $arSelect);

        $arElements = $this->getElements($arSelect);
        $arSections = $this->getSections($arElements);

        $arResult = [];

        foreach ($arElements as $element) {
            $arResult[] = [
                'NAME' => $element['NAME'],
                'DESCRIPTION' => $element['PREVIEW_TEXT'] ?: $element['NAME'],
                'DATE' => SolutionUtils::getIsoDate($element),
                'DETAIL_URL' => $this->getDetailUrl($element),
                'SECTION' => $this->getSectionName($element, $arSections),
                'IMAGE' => $this->getElementImageUrl($element),
            ];
        }

        return $arResult;
    }

    protected function getElements(array $arSelect): array
    {
        return \TSolution\Cache::CIblockElement_GetList(
            [
                'CACHE' => [
                    'TAG' => \TSolution\Cache::GetIBlockCacheTag($this->arParams['IBLOCK_ID']),
                    'MULTI' => 'Y',
                ],
                $this->arParams['SORT_BY1'] => $this->arParams['SORT_ORDER1'],
                $this->arParams['SORT_BY2'] => $this->arParams['SORT_ORDER2'],
            ],
            array_merge($this->arItemFilter, (array)$GLOBALS[$this->arParams["FILTER_NAME"]]),
            false,
            $this->getNavParams(),
            $arSelect
        );
    }

    protected function getNavParams(): array
    {
        $context = Application::getInstance()->getContext();
        $numPage = $context->getRequest()->get('PAGEN_1') ?? 1;

        return [
            'iNumPage' => $numPage,
            'nPageSize' => $this->arParams['NEWS_COUNT'],
        ];
    }

    protected function getSections(array $elements): array
    {
        $sectionIds = [];

        foreach ($elements as $element) {
            $ids = (array) ($element['IBLOCK_SECTION_ID'] ?? []);
            $sectionIds = array_merge($sectionIds, $ids);
        }

        $sectionIds = array_unique($sectionIds);
        $sectionMap = [];

        if ($sectionIds) {
            $arSections = \TSolution\Cache::CIBlockSection_GetList(
                [
                    'CACHE' => [
                        'TAG' => \TSolution\Cache::GetIBlockCacheTag($this->arParams['IBLOCK_ID']),
                        'MULTI' => 'Y',
                    ],
                ],
                ['ID' => $sectionIds],
                false,
                ['ID', 'NAME']
            );

            foreach ($arSections as $section) {
                $sectionMap[$section['ID']] = $section['NAME'];
            }
        }

        return $sectionMap;
    }

    protected function getDetailUrl(array $element): string
    {
        $sectionID = $element['IBLOCK_SECTION_ID_SELECTED'];

        if (is_array($element['DETAIL_PAGE_URL'])) {
            $detailUrl = $element['DETAIL_PAGE_URL'][$sectionID];
        } else {
            $detailUrl = $element['DETAIL_PAGE_URL'];
        }

        return $this->siteUrl.$detailUrl ?? '';
    }

    protected function getSectionName(array $element, array $sections): string
    {
        if (empty($element['IBLOCK_SECTION_ID'])) {
            return '';
        }

        $ids = (array) $element['IBLOCK_SECTION_ID'];

        return implode(', ', array_map(fn ($id) => $sections[$id] ?? '', $ids));
    }

    protected function getElementImageUrl(array $element): string
    {
        $image = $element['PREVIEW_PICTURE'] ?? $element['DETAIL_PICTURE'];
        $path = $image ? \CFile::GetPath($image) : SITE_TEMPLATE_PATH.'/images/svg/noimage_content.svg';

        return $this->siteUrl.$path;
    }
}
