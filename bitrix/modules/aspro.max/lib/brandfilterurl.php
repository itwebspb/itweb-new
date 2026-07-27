<?php
namespace Aspro\Max;

class BrandFilterUrl
{
    protected array $arResult;
    protected array $arBrand;

    public function __construct(array $arResult, array $arBrand)
    {
        $this->arResult = $arResult;
        $this->arBrand  = $arBrand;
    }

    public function getCatalogFilterPath(): string
    {
        $sectionList = \CIBlockSection::GetByID($this->arResult['IBLOCK_SECTION_ID']);
        $sectionList->SetUrlTemplates(
            $this->arResult['ORIGINAL_PARAMETERS']['SEF_URL_TEMPLATES']['smart_filter']
        );
        $section = $sectionList->GetNext();

        if (!$section) {
            return '';
        }

        $path = $this->arResult['LIST_PAGE_URL'] . $section['DETAIL_PAGE_URL'];

        return $this->makeFilterUrl($path);
    }

    public function makeFilterUrl(string $url): string
    {
        if (empty($this->arBrand['CODE'])) {
            return '';
        }

        $filterValue = 'brand-is-' . $this->arBrand['CODE'];

        return str_replace('#SMART_FILTER_PATH#', $filterValue, $url);
    }
}

