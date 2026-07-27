<?php

namespace Aspro\Sku\Admin\Traits;

trait Singletonable
{
    private static $instance;

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
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
