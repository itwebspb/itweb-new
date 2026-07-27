<?php

namespace Aspro\Sku\Events;

use Aspro\Sku\Utils;

class Page
{
    public static function onEndBufferContentHandler(string &$content)
    {
        if (!defined('ADMIN_SECTION') && defined(Utils::getEventConstName())) {
            $markerName = Utils::getComponentMarkerName();
            $templateMarker = '<!-- __'.$markerName.'__ -->';
            if (strpos($content, $templateMarker)) {
                $content = str_replace($templateMarker, static::getComponentByMarker($content, $markerName), $content);
            }
        }
    }

    protected static function getComponentByMarker(string &$content, string $markerName): string
    {
        $component = '';

        if (defined($markerName)) {
            $componentMatches = [];

            preg_match('/<!--'.$markerName.'-->\s*?<div\s*?hidden>(.*?)<\/div>\s*?<!--\/'.$markerName.'-->/s', $content, $componentMatches);
            if (!empty($componentMatches[1])) {
                $component = $componentMatches[1];
                $content = str_replace($componentMatches[0], '', $content);
            }
            unset($componentMatches);
        }

        return $component;
    }
}
