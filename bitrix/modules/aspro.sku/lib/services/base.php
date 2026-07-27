<?php

namespace Aspro\Sku\Services;

use Aspro\Sku\Traits;

abstract class Base
{
    use Traits\Singletonable;
    use Traits\SkuContextable;
}
