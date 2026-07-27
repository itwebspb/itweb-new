<?php

namespace Aspro\Max\Product;

class MetaInfo
{
    private static $instance = null;

    /**
     * @var array{
     *     PAGE_TITLE: mixed,
     *     META_TITLE: mixed,
     *     META_DESCRIPTION: mixed,
     *     META_KEYWORDS: mixed,
     * }
     */
    private array $info = [];

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
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function collect(array $item)
    {
        $this->info['PAGE_TITLE'] = Common::getElementName($item);
        $this->info['META_TITLE'] = (!empty($item['IPROPERTY_VALUES']['ELEMENT_META_TITLE']) ? $item['IPROPERTY_VALUES']['ELEMENT_META_TITLE'] : $item['NAME']);
        $this->info['META_DESCRIPTION'] = (!empty($item['IPROPERTY_VALUES']['ELEMENT_META_DESCRIPTION']) ? $item['IPROPERTY_VALUES']['ELEMENT_META_DESCRIPTION'] : '');
        $this->info['META_KEYWORDS'] = (!empty($item['IPROPERTY_VALUES']['ELEMENT_META_KEYWORDS']) ? $item['IPROPERTY_VALUES']['ELEMENT_META_KEYWORDS'] : '');

        $this->info = array_filter($this->info);
    }

    public function set()
    {
        global $APPLICATION;

        if (empty($this->info)) {
            return;
        }

        if ($this->info['PAGE_TITLE']) {
            $APPLICATION->SetTitle($this->info['PAGE_TITLE']);
        }
        if ($this->info['META_TITLE']) {
            $APPLICATION->SetPageProperty('title', $this->info['META_TITLE']);
        }
        if ($this->info['META_DESCRIPTION']) {
            $APPLICATION->SetPageProperty('description', $this->info['META_DESCRIPTION']);
        }
        if ($this->info['META_KEYWORDS']) {
            $APPLICATION->SetPageProperty('keywords', $this->info['META_KEYWORDS']);
        }
    }
}
