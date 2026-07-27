<?php

namespace Aspro\Sku\DTO;

class SkuContext
{
    private function __construct(
        public int $iblockId,
        public int $elementId,
        public int $sectionId,
        public array $sort = [],
        public array $options = [],
        public string $useRegion = 'N',
        public array $stores = [],
        public ?array $parentComponentParams = [],
    ) {
    }

    public static function createFromComponentParams(array $params, ?array $parentComponentParams = []): self
    {
        return new self(
            iblockId: (int) ($params['IBLOCK_ID'] ?? 0),
            elementId: (int) ($params['ID'] ?? 0),
            sectionId: (int) ($params['SECTION_ID'] ?? 0),
            sort: [
                'field' => $params['SKU_SORT_FIELD'] ?? $params['OFFERS_SORT_FIELD'] ?? 'sort',
                'order' => $params['SKU_SORT_ORDER'] ?? $params['OFFERS_SORT_ORDER'] ?? 'asc',
                'field2' => $params['SKU_SORT_FIELD2'] ?? $params['OFFERS_SORT_FIELD2'] ?? 'name',
                'order2' => $params['SKU_SORT_ORDER2'] ?? $params['OFFERS_SORT_ORDER2'] ?? 'asc',
            ],
            options: [
                'SHOW_HINT' => $params['SHOW_HINT'] ?? 'Y',
                'SHOW_TEXT_FOR_EMPTY_PICTURES' => $params['SHOW_TEXT_FOR_EMPTY_PICTURES'] ?? 'Y',
                'SKU_SHOW_PREVIEW_PICTURE_PROPS' => $params['SKU_SHOW_PREVIEW_PICTURE_PROPS'] ?? [],
                'COMPONENT_MARKER' => $params['COMPONENT_MARKER'] ?? '',
                'SEF_URL_ELEMENT' => $params['SEF_URL_ELEMENT'],
                'EXTERNAL_FILTER' => $params['EXTERNAL_FILTER'],
                'USE_AVAILABILITY' => $params['USE_AVAILABILITY'],
                'USE_FEATURE_PROPS' => $params['USE_FEATURE_PROPS'],
                'USE_MAIN_ELEMENT_SECTION' => $params['USE_MAIN_ELEMENT_SECTION'] ?? 'N',
            ],
            useRegion: ($params['USE_REGION'] === 'Y' ? 'Y' : 'N'),
            stores: $params['STORES'] ?? [],
            parentComponentParams: $parentComponentParams,
        );
    }

    public function toComponentParams(): array
    {
        return [
            'IBLOCK_ID' => $this->iblockId,
            'ID' => $this->elementId,
            'SECTION_ID' => $this->sectionId,
            'SKU_SORT_FIELD' => $this->sort['field'],
            'SKU_SORT_ORDER' => $this->sort['order'],
            'SKU_SORT_FIELD2' => $this->sort['field2'],
            'SKU_SORT_ORDER2' => $this->sort['order2'],
        ] + $this->options;
    }
}
