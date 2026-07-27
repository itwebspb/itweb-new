<?php

namespace Aspro\Max\Comment;

use CMax as Solution;

class Like
{
    private int $userId;
    private int $commentId;
    private string $action;

    public function __construct(int $userId, int $commentId, string $action)
    {
        $this->userId = $userId;
        $this->commentId = $commentId;
        $this->action = $action;
    }

    public function vote(): array
    {
        global $USER_FIELD_MANAGER;

        $ufId = ($this->userId % 1000).($this->commentId % 1000);
        $fields = $USER_FIELD_MANAGER->GetUserFields('BLOG_COMMENT', $this->commentId);
        $fieldValueId = $USER_FIELD_MANAGER->GetUserFields('BLOG_COMMENT_ID', $ufId);

        return array_merge(
            $this->processLike($ufId, $fields, $fieldValueId),
            $this->processDislike($ufId, $fields, $fieldValueId),
        );
    }

    private function processLike(string $ufId, array $fields, array $fieldValueId): array
    {
        global $USER_FIELD_MANAGER;

        $value = (int)($fields['UF_ASPRO_COM_LIKE']['VALUE'] ?? 0);
        $ids = Solution::unserialize((string)($fieldValueId['UF_LIKE_ID']['VALUE'] ?? ''));

        if (!isset($ids[$this->userId])) {
            $ids[$this->userId] = 'N';
        }

        if ($ids[$this->userId] === 'Y') {
            $ids[$this->userId] = 'N';
            $value--;
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT_ID', $ufId, ['UF_LIKE_ID' => serialize($ids)]);
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT', $this->commentId, ['UF_ASPRO_COM_LIKE' => $value]);
            return ['LIKE' => $value, 'SET_ACTIVE_LIKE' => false];
        }

        if ($this->action === 'like') {
            $ids[$this->userId] = 'Y';
            $value++;
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT_ID', $ufId, ['UF_LIKE_ID' => serialize($ids)]);
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT', $this->commentId, ['UF_ASPRO_COM_LIKE' => $value]);
            return ['LIKE' => $value, 'SET_ACTIVE_LIKE' => true];
        }

        return ['LIKE' => $value];
    }

    private function processDislike(string $ufId, array $fields, array $fieldValueId): array
    {
        global $USER_FIELD_MANAGER;

        $value = (int)($fields['UF_ASPRO_COM_DISLIKE']['VALUE'] ?? 0);
        $ids = Solution::unserialize((string)($fieldValueId['UF_DISLIKE_ID']['VALUE'] ?? ''));

        if (!isset($ids[$this->userId])) {
            $ids[$this->userId] = 'N';
        }

        if ($ids[$this->userId] === 'Y') {
            $ids[$this->userId] = 'N';
            $value--;
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT_ID', $ufId, ['UF_DISLIKE_ID' => serialize($ids)]);
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT', $this->commentId, ['UF_ASPRO_COM_DISLIKE' => $value]);
            return ['DISLIKE' => $value, 'SET_ACTIVE_DISLIKE' => false];
        }

        if ($this->action === 'dislike') {
            $ids[$this->userId] = 'Y';
            $value++;
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT_ID', $ufId, ['UF_DISLIKE_ID' => serialize($ids)]);
            $USER_FIELD_MANAGER->Update('BLOG_COMMENT', $this->commentId, ['UF_ASPRO_COM_DISLIKE' => $value]);
            return ['DISLIKE' => $value, 'SET_ACTIVE_DISLIKE' => true];
        }

        return ['DISLIKE' => $value];
    }
}
