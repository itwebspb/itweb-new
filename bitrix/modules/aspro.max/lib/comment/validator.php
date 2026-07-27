<?php

namespace Aspro\Max\Comment;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class Validator
{
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;

    public static function checkRating(int|string $value): void
    {
        if ($value < self::MIN_RATING || $value > self::MAX_RATING) {
            throw new SystemException(Loc::getMessage('ERROR__INVALID_RATING_VALUE'));
        }
    }

    public static function checkImageFile(array $image, float $maxSize): void
    {
        if ($fileErrorMessage = \CFile::CheckImageFile($image, $maxSize)) {
            throw new SystemException($fileErrorMessage);
        }
    }
}
