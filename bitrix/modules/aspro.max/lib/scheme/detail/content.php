<?php

namespace Aspro\Max\Scheme\Detail;

use Aspro\Max\Utils as SolutionUtils;
use Bitrix\Main\Application;

abstract class Content
{
    abstract public function buildSchema(): array;

    protected string $siteName = '';
    protected string $siteUrl = '';
    protected string $currentUrl = '';

    protected array $arParams;
    protected array $arItem;

    public function __construct(array $arParams, array $arItem)
    {
        $this->arParams = $arParams;
        $this->arItem = $arItem;
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
}
