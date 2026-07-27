<?php

namespace Aspro\Sku\Admin\Page\Ui;

abstract class Section extends Layout
{
    abstract public function getFilterFields(): array;

    abstract public function getContextMenu(): array;

    abstract public function getGridColumns(): array;
}
