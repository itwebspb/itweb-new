<?php

namespace Aspro\Sku\Traits;

trait Singletonable
{
    private static $instance = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function getInstance(): static
    {
        if (!static::$instance[static::class]) {
            static::$instance[static::class] = new static();
        }

        return static::$instance[static::class];
    }
}
