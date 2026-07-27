<?php

use Aspro\Sku\Components\List\Base as SkuListBase;
use Aspro\Sku\Config;
use Aspro\Sku\Engine;
use Aspro\Sku\General;
use Aspro\Sku\Utils;
use Bitrix\Main\Config\Option;

class SkuList extends SkuListBase
{
    public function onPrepareComponentParams($arParams)
    {
        if (!Config::getEnabled(SITE_ID)) {
            return $arParams;
        }

        $this->extendParamsByParentComponent($arParams, $this->getParent()?->arParams ?? []);

        $arParams['COMPONENT_MARKER'] = Utils::getComponentMarkerName();

        $arParams['SKU_SHOW_PREVIEW_PICTURE_PROPS'] = Option::get(General::moduleId, 'SKU_SHOW_PREVIEW_PICTURE_PROPS', '', SITE_ID);
        if ($arParams['SKU_SHOW_PREVIEW_PICTURE_PROPS']) {
            $arParams['SKU_SHOW_PREVIEW_PICTURE_PROPS'] = explode(',', strtolower($arParams['SKU_SHOW_PREVIEW_PICTURE_PROPS']));
        }

        $arParams['SHOW_HINT'] = $arParams['SHOW_HINT'] ?: Option::get(General::moduleId, 'SHOW_HINTS', 'Y', SITE_ID);
        $arParams['SHOW_TEXT_FOR_EMPTY_PICTURES'] = Option::get(General::moduleId, 'SHOW_TEXT_FOR_EMPTY_PICTURES', 'Y', SITE_ID);

        return $arParams;
    }

    private function extendParamsByParentComponent(array &$arParams, array $parentComponentParams = [])
    {
        $arParams['CACHE_TYPE'] = $arParams['CACHE_TYPE'] ?? $parentComponentParams['CACHE_TYPE'] ?? 'A';
        $arParams['CACHE_TIME'] = $arParams['CACHE_TIME'] ?? $parentComponentParams['CACHE_TIME'] ?? '86000';

        $sefFolder = $parentComponentParams['SEF_FOLDER'] ?? '/catalog/';
        $arParams['SEF_URL_ELEMENT'] = ($parentComponentParams['SEF_MODE'] ?? 'N') === 'Y'
            ? $sefFolder.$parentComponentParams['SEF_URL_TEMPLATES']['element']
            : $sefFolder.'?SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#';

        $arParams['EXTERNAL_FILTER'] = self::getGlobalFilter($arParams['FILTER_NAME'] ?? $parentComponentParams['FILTER_NAME'] ?? '');

        $arParams['SKU_SORT_FIELD'] = $arParams['SKU_SORT_FIELD'] ?? $parentComponentParams['SKU_SORT_FIELD'] ?? 'sort';
        $arParams['SKU_SORT_ORDER'] = $arParams['SKU_SORT_ORDER'] ?? $parentComponentParams['SKU_SORT_ORDER'] ?? 'asc';
        $arParams['SKU_SORT_FIELD2'] = $arParams['SKU_SORT_FIELD2'] ?? $parentComponentParams['SKU_SORT_FIELD2'] ?? 'name';
        $arParams['SKU_SORT_ORDER2'] = $arParams['SKU_SORT_ORDER2'] ?? $parentComponentParams['SKU_SORT_ORDER2'] ?? 'asc';
        $arParams['USE_MAIN_ELEMENT_SECTION'] = $arParams['USE_MAIN_ELEMENT_SECTION'] ?? $parentComponentParams['USE_MAIN_ELEMENT_SECTION'] ?? 'N';
    }

    private function getGlobalFilter(string $filterName): array
    {
        return (array) ($GLOBALS[$filterName] ?? []);
    }

    public function executeComponent()
    {
        if (!Config::getEnabled(SITE_ID)) {
            return;
        }

        $eventConstName = Utils::getEventConstName();
        if (!defined($eventConstName) && Engine::hasAnyActiveRules()) {
            define($eventConstName, true);
        }

        if ($this->useAvailability()) {
            $GLOBALS['APPLICATION']->IncludeComponent(
                'aspro:sku.list.section',
                $this->getTemplateName(),
                $this->arParams,
                $this->getParent(),
                ['HIDE_ICONS' => 'Y']
            );

            return;
        }

        if ($this->StartResultCache(false)) {
            $this->IncludeComponentTemplate();
        }
    }

    private function useAvailability(): bool
    {
        return Config::useAvailability(SITE_ID) && !in_array($this->GetTemplateName(), ['', '.default']);
    }
}
