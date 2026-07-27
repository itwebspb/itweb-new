<?php

namespace Aspro\Sku\Traits;

use Aspro\Sku\DTO\SkuContext;

trait SkuContextable
{
    private ?SkuContext $context = null;

    public function setContext(SkuContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): ?SkuContext
    {
        return $this->context;
    }
}
