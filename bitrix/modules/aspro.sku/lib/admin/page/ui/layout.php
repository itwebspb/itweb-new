<?php

namespace Aspro\Sku\Admin\Page\Ui;

use Aspro\Sku\Admin\Traits\Singletonable;

class Layout
{
    use Singletonable;

    public function getGridId()
    {
        return strtolower(implode('_', array_filter(explode('\\', str_replace(__NAMESPACE__, '', static::class)))));
    }
}
