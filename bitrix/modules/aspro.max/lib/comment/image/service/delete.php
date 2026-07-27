<?php

namespace Aspro\Max\Comment\Image\Service;

use Aspro\Max\Comment\Image\BlogImage;

class Delete
{
    private int $commentId;

    public function __construct(int|string $commentId)
    {
        $this->commentId = (int) $commentId;
    }

    public function removeDeletedImages(): void
    {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest()->toArray();
        $deletedImagesIds = $request['deleted_images'];

        if (empty($deletedImagesIds)) {
            return;
        }

        $this->deleteImages(['@FILE_ID' => $deletedImagesIds]);
    }

    public function deleteImages(array $additionalFilter = []): void
    {
        $filter = array_merge(['COMMENT_ID' => $this->commentId], $additionalFilter);

        $imageList = BlogImage::getList($filter);
        if (empty($imageList)) {
            return;
        }

        foreach ($imageList as $image) {
            \CFile::Delete($image['FILE_ID']);
            BlogImage::delete((int) $image['ID']);
        }
    }
}
