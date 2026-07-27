<?php

namespace Aspro\Max\Comment;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Helper
{
    public static function getApplicationExceptionMessage(string $fallbackMessage = ''): string
    {
        if ($exception = $GLOBALS['APPLICATION']->GetException()) {
            return $exception->GetString();
        }

        return Loc::getMessage($fallbackMessage);
    }
}
