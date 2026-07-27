<?php

namespace Aspro\Max\Comment\Image;

use Aspro\Max\Comment\Helper;
use Bitrix\Main\SystemException;

class BlogImage
{
    public static function getCommentImagesCount(int $postId, int $blogId, int $commentId): int
    {
        return \CBlogImage::GetList(
            [],
            ['POST_ID' => $postId, 'BLOG_ID' => $blogId, 'COMMENT_ID' => $commentId],
            ['ID'],
            ['nTopCount' => false]
        )->NavRecordCount;
    }

    public static function addImageToComment(array $options = []): void
    {
        $result = \CBlogImage::Add($options);

        if (!$result) {
            throw new SystemException(Helper::getApplicationExceptionMessage('ERROR__BLOG_IMAGE_ADD'));
        }
    }

    public static function getList(array $filter): array
    {
        $imageList = [];

        $resImages = \CBlogImage::GetList(arFilter: $filter);
        while ($arImage = $resImages->Fetch()) {
            $imageList[] = $arImage;
        }

        return $imageList;
    }

    public static function delete(int $id): void
    {
        \CBlogImage::Delete($id);
    }
}
