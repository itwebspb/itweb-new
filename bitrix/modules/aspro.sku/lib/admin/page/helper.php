<?php

namespace Aspro\Sku\Admin\Page;

use Bitrix\Main\Web\Json;

class Helper
{
    public static function failureJsonResponse(string $message = '', array $additional = [])
    {
        echo Json::encode([
            'result' => false,
            'message' => $message,
        ] + $additional);
    }

    public static function successJsonResponse(string $message = '', array $additional = [])
    {
        echo Json::encode([
            'result' => true,
            'message' => $message,
        ] + $additional);
    }

    public static function throwException($messages): void
    {
        $message = is_array($messages) ? implode('<br/> ', $messages) : $messages;

        throw new \Exception($message);
    }
}
