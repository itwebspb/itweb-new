<?php

namespace Aspro\Max\Controller;

use Aspro\Max\Comment\Like;
use Aspro\Max\Comment\Like\Fields;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;

class CommentLike extends Controller
{
    public function configureActions(): array
    {
        return [
            'vote' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function voteAction(int $commentId, string $action): ?array
    {
        global $USER;

        if ($commentId <= 0 || !in_array($action, ['like', 'dislike'], true)) {
            $this->addError(new Error('Invalid parameters'));
            return null;
        }

        Fields::ensure();

        return (new Like((int)$USER->GetID(), $commentId, $action))->vote();
    }
}
