<?php

namespace Aspro\Max\Scheme\Detail;

use Aspro\Max\Hl\Helper;
use Aspro\Max\Utils as SolutionUtils;

class News extends Content
{
    public function buildSchema(): array
    {
        if (!$this->arItem) {
            return [];
        }

        $imageSrc = SolutionUtils::getAbsolutePath(
            \CFile::GetPath(
                $this->arItem['PREVIEW_PICTURE']
                ?: $this->arItem['DETAIL_PICTURE']
                ?: 0
            ) ?: SITE_TEMPLATE_PATH . '/images/svg/noimage_product.svg'
        );

        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'url' => $this->currentUrl,
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->siteName,
            ],
            'image' => $imageSrc,
            'headline' => $this->arItem['NAME'] ?? '',
            'articleBody' => strip_tags(
                $this->arItem['DETAIL_TEXT']
                ?? $this->arItem['PREVIEW_TEXT']
                ?? ''
            ),
            'datePublished' => SolutionUtils::getIsoDate($this->arItem),
            'author' => $this->getAuthorSchema(),
        ];
    }

    protected function getAuthorSchema(): array
    {
        $authorSchema = [
            '@type' => 'Organization',
            'name' => $this->siteName,
            'url' => $this->siteUrl,
        ];

        if (!empty($this->arItem['PROPERTY_AUTHOR_REF_VALUE'])) {
            $res = \CIBlockElement::GetProperty(
                $this->arItem['IBLOCK_ID'],
                $this->arItem['ID'],
                [],
                ['CODE' => 'AUTHOR_REF']
            );

            if ($prop = $res->Fetch()) {
                $tableName = $prop['USER_TYPE_SETTINGS']['TABLE_NAME'] ?? '';

                if ($tableName && ($dataManager = Helper::getInstance($tableName))) {
                    $author = $dataManager->get([
                        'select' => ['UF_NAME'],
                        'filter' => [
                            'UF_XML_ID' => $prop['VALUE'],
                        ],
                    ]);

                    if ($authorName = ($author[0]['UF_NAME'] ?? '')) {
                        $authorSchema = [
                            '@type' => 'Person',
                            'name' => $authorName,
                            'url' => $this->siteUrl,
                        ];
                    }
                }
            }
        }

        return $authorSchema;
    }
}
