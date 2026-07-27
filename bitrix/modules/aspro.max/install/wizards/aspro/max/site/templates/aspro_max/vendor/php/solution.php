<?php

namespace {
    if (!defined('VENDOR_PARTNER_NAME')) {
        /* @const Aspro partner name */
        define('VENDOR_PARTNER_NAME', 'aspro');
    }

    if (!defined('VENDOR_SOLUTION_NAME')) {
        /* @const Aspro solution name */
        define('VENDOR_SOLUTION_NAME', 'max');
    }

    if (!defined('VENDOR_MODULE_ID')) {
        /* @const Aspro module id */
        define('VENDOR_MODULE_ID', 'aspro.max');
    }

    foreach ([
        'CMax' => 'TSolution',
        'CMaxCache' => 'TSolution\Cache',
        'CMaxCondition' => 'TSolution\Condition',
        'CMaxEvents' => 'TSolution\Events',
        'CMaxRegionality' => 'TSolution\Regionality',
        'Aspro\Functions\CAsproMax' => 'TSolution\Functions',
        'Aspro\Functions\CAsproMaxItem' => 'TSolution\Functions\Item',
        'Aspro\Max\Author' => 'TSolution\Author',
        'Aspro\Max\BrandFilterUrl' => 'TSolution\BrandFilterUrl',
        'Aspro\Max\CacheableUrl' => 'TSolution\CacheableUrl',
        'Aspro\Max\Captcha' => 'TSolution\Captcha',
        'Aspro\Max\Captcha\Service' => 'TSolution\Captcha\Service',
        'Aspro\Max\Filter' => 'TSolution\Filter',
        'Aspro\Max\Functions\Extensions' => 'TSolution\Extensions',
        'Aspro\Max\Hl\Helper' => 'TSolution\HelperHl',
        'Aspro\Max\LinkableProperty' => 'TSolution\LinkableProperty',
        'Aspro\Max\PhoneAuth' => 'TSolution\PhoneAuth',
        'Aspro\Max\Product\CCatalog' => 'TSolution\Product\CCatalog',
        'Aspro\Max\Product\Common' => 'TSolution\Product\Common',
        'Aspro\Max\Product\MetaInfo' => 'TSolution\Product\MetaInfo',
        'Aspro\Max\Product\Price' => 'TSolution\Product\Price',
        'Aspro\Max\Product\Vat' => 'TSolution\Product\Vat',
        'Aspro\Max\Scheme\Common' => 'TSolution\Scheme\Common',
        'Aspro\Max\Social\Factory' => 'TSolution\Social\Factory',
        'Aspro\Max\Social\Video\Factory' => 'TSolution\Social\Video\Factory',
        'Aspro\Max\Template\CatalogSort' => 'TSolution\Template\CatalogSort',
        'Aspro\Max\Template\Common\IncludeAreas' => 'TSolution\Template\Common\IncludeAreas',
        'Aspro\Max\Traits\Consent' => 'TSolution\Consent',
        'Aspro\Max\Utils' => 'TSolution\Utils',
        'Aspro\Max\Validation' => 'TSolution\Validation',
        'Aspro\Max\Vendor' => 'TSolution\Vendor',
    ] as $original => $alias) {
        if (!class_exists($alias)) {
            class_alias($original, $alias);
        }
    }

    // these alias declarations for IDE only
    if (false) {
        class TSolution extends CMax
        {
        }
    }
}

// these alias declarations for IDE only

namespace TSolution {
    if (false) {
        class Cache extends \CMaxCache
        {
        }
        class Condition extends \CMaxCondition
        {
        }
        class Consent extends \Aspro\Max\Consent
        {
        }
        class Events extends \CMaxEvents
        {
        }
        class Functions extends \Aspro\Functions\CAsproMax
        {
        }
        class Extensions extends \Aspro\Max\Functions\Extensions
        {
        }
        class Regionality extends \CMaxRegionality
        {
        }
        class Utils extends \Aspro\Max\Utils
        {
        }
        class Filter extends \Aspro\Max\Filter
        {
        }
        class CacheableUrl extends \Aspro\Max\CacheableUrl
        {
        }
        class PhoneAuth extends \Aspro\Max\PhoneAuth
        {
        }
        class LinkableProperty extends \Aspro\Max\LinkableProperty
        {
        }
        class Validation extends \Aspro\Max\Validation
        {
        }
        class Author extends \Aspro\Max\Author
        {
        }
        class HelperHl extends Aspro\Max\Hl\Helper
        {
        }
        class Vendor extends \Aspro\Max\Vendor
        {
        }
    }
}

namespace TSolution\Functions {
    if (false) {
        class Item extends \Aspro\Functions\CAsproMaxItem
        {
        }
    }
}

namespace TSolution\Social {
    if (false) {
        class Factory extends \Aspro\Max\Social\Factory
        {
        }
    }
}

namespace TSolution\Social\Video {
    if (false) {
        class Factory extends \Aspro\Max\Social\Video\Factory
        {
        }
    }
}

namespace TSolution\Captcha {
    if (false) {
        class Service extends \Aspro\Max\Captcha\Service
        {
        }
    }
}

namespace TSolution\Product {
    if (false) {
        class CCatalog extends \Aspro\Max\Product\CCatalog
        {
        }
        class Common extends \Aspro\Max\Product\Common
        {
        }
        class MetaInfo extends \Aspro\Max\Product\MetaInfo
        {
        }
        class Price extends \Aspro\Max\Product\Price
        {
        }
        class Vat extends \Aspro\Max\Product\Vat
        {
        }
    }
}

namespace TSolution\Template {
    if (false) {
        class CatalogSort extends \Aspro\Max\Template\CatalogSort
        {
        }
    }
}

namespace TSolution\Template\Common {
    if (false) {
        class IncludeAreas extends \Aspro\Max\Template\Common\IncludeAreas
        {
        }
    }
}
