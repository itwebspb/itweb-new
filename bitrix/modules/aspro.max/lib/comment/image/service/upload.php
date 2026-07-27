<?php

namespace Aspro\Max\Comment\Image\Service;

use Aspro\Max\Comment\Helper;
use Aspro\Max\Comment\Image\BlogImage;
use Aspro\Max\Comment\Validator as CommentValidator;
use Bitrix\Main\Application;
use Bitrix\Main\Session\SessionInterface;
use Bitrix\Main\SystemException;

class Upload
{
    private SessionInterface $session;

    private int $maxImageCount = 10;
    private float $maxImageSize = 0.5;

    private int $commentId;
    private int $blogId;
    private int $postId;

    private array $errorList = [];

    public function __construct(int|string $commentId, array $arFields)
    {
        $this->commentId = (int) $commentId;
        $this->blogId = (int) $arFields['BLOG_ID'];
        $this->postId = (int) $arFields['POST_ID'];

        $this->session = Application::getInstance()->getSession();
        if ($this->session->get('BLOG_MAX_IMAGE_COUNT') > 0) {
            $this->maxImageCount = $this->session->get('BLOG_MAX_IMAGE_COUNT');
        }

        if ($this->session->get('BLOG_MAX_IMAGE_SIZE') > 0) {
            $this->maxImageSize = $this->session->get('BLOG_MAX_IMAGE_SIZE');
        }
        $this->maxImageSize *= 1024 * 1024;
    }

    public function attachImages(): void
    {
        $maxImagesAmount = $this->getAvailableImagesCount();
        if (!$maxImagesAmount) {
            return;
        }

        $listImages = $this->getNormalizedImagesList($maxImagesAmount);
        if (empty($listImages)) {
            return;
        }

        foreach ($listImages as $image) {
            try {
                CommentValidator::checkImageFile($image, $this->maxImageSize);

                $fileId = $this->saveFile($image);
                BlogImage::addImageToComment([
                    'FILE_ID' => $fileId,
                    'POST_ID' => $this->postId,
                    'BLOG_ID' => $this->blogId,
                    'COMMENT_ID' => $this->commentId,
                    'IMAGE_SIZE' => $image['size'],
                ]);
            } catch (SystemException $exception) {
                $this->errorList[] = [
                    'FILE_NAME' => $image['name'],
                    'MESSAGE' => $exception->getMessage(),
                ];
            }
        }

        if ($this->errorList) {
            $this->session->set('NOT_ADDED_FILES', ['ID' => $this->commentId, 'FILES' => $this->errorList]);
        }
    }

    private function getAvailableImagesCount(): int
    {
        return $this->maxImageCount - BlogImage::getCommentImagesCount($this->postId, $this->blogId, $this->commentId);
    }

    private function getNormalizedImagesList(int $maxImagesAmount): array
    {
        $listImages = [];

        foreach ($_FILES['comment_images'] as $key => $items) {
            foreach ($items as $index => $value) {
                $listImages[$index][$key] = $value;
            }
        }
        unset($_FILES['comment_images']);

        $this->filterInvalidImages($listImages);

        return array_slice($listImages, 0, $maxImagesAmount);
    }

    private function filterInvalidImages(array &$listImages): void
    {
        $listImages = array_filter($listImages, fn ($image) => $image['error'] === UPLOAD_ERR_OK);
    }

    private function saveFile(array $image): int
    {
        if ($fileId = \CFile::SaveFile($image, '/blog/comment/')) {
            return (int) $fileId;
        }

        throw new SystemException(Helper::getApplicationExceptionMessage('ERROR__SAVE_FILE'));
    }
}
