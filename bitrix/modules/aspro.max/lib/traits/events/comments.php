<?php

namespace Aspro\Max\Traits\Events;

use Aspro\Max\Comment\Image\Service;
use Aspro\Max\Comment\Validator as CommentValidator;
use Aspro\Max\Validation;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use CMax as Solution;
use CSaleOrder;

Loc::loadMessages(__FILE__);

trait Comments
{
    public static function OnBeforeCommentAddHandler(&$arFields)
    {
        try {
            $request = Context::getCurrent()->getRequest();

            if (Validation::isSolutionForm()) {
                if (Validation::isAgreementsRequired()) {
                    if (empty($request->get('licenses_popup'))) {
                        throw new SystemException(Loc::getMessage('ERROR_FORM_LICENSE'));
                    }
                }
            }

            if (isset($request['rating'])) {
                if ($request['rating']) {
                    CommentValidator::checkRating($request['rating']);

                    $arFields['UF_ASPRO_COM_RATING'] = $request['rating'];

                    global $USER;
                    $userID = $USER->GetID();
                    if ($userID) {
                        $arFilter = ['USER_ID' => $userID];
                        if (strpos($request['XML_ID'], '%') !== false) {
                            $arFilter['%=BASKET_PRODUCT_XML_ID'] = str_replace('%', '#%', $request['XML_ID']);
                        } else {
                            $arFilter['BASKET_PRODUCT_ID'] = $request['ELEMENT_ID'];
                        }

                        $arFields['UF_ASPRO_COM_APPROVE'] = CSaleOrder::GetList([], $arFilter, false, false)->SelectedRowsCount() > 0;
                    }
                } elseif (!$request['parentId']) {
                    throw new SystemException(Loc::getMessage('RATING_IS_REQUIRED'));
                }
            }

            if (isset($arFields['AUTHOR_NAME'])) {
                $arFields['AUTHOR_NAME'] = strip_tags($arFields['AUTHOR_NAME']);
            }

            if (isset($arFields['POST_TEXT'])) {
                $arFields['POST_TEXT'] = strip_tags($arFields['POST_TEXT'], '<virtues><limitations><comment>');
            }
        } catch (SystemException $exception) {
            $GLOBALS['APPLICATION']->ThrowException($exception->getMessage());

            return false;
        }
    }

    public static function OnCommentAddHandler($commentID, &$arFields)
    {
        (new Service\Upload($commentID, $arFields))->attachImages();

        Solution::updateExtendedReviewsProps($commentID);
    }

    public static function OnBeforeCommentUpdateHandler($id, &$arFields)
    {
        try {
            $request = Context::getCurrent()->getRequest();

            if (Validation::isSolutionForm()) {
                if (Validation::isAgreementsRequired()) {
                    if (empty($request->get('licenses_popup'))) {
                        throw new SystemException(Loc::getMessage('ERROR_FORM_LICENSE'));
                    }
                }
            }

            if (isset($request['approve_comment_id']) || isset($request['unapprove_comment_id'])) {
                if (!check_bitrix_sessid() || !$GLOBALS['USER']->IsAdmin()) {
                    throw new SystemException(Loc::getMessage('ERROR_ACCESS_DENIED'));
                }
                $bStatus = isset($request['approve_comment_id']);
                $GLOBALS['USER_FIELD_MANAGER']->Update('BLOG_COMMENT', $id, ['UF_ASPRO_COM_APPROVE' => $bStatus]);
            }

            if (isset($request['rating'])) {
                CommentValidator::checkRating($request['rating']);

                $GLOBALS['USER_FIELD_MANAGER']->Update('BLOG_COMMENT', $id, ['UF_ASPRO_COM_RATING' => (int) $request['rating']]);
            }

            if (isset($arFields['POST_TEXT'])) {
                $arFields['POST_TEXT'] = strip_tags($arFields['POST_TEXT'], '<virtues><limitations><comment>');
            }
        } catch (SystemException $exception) {
            $GLOBALS['APPLICATION']->ThrowException($exception->getMessage());

            return false;
        }
    }

    public static function OnCommentUpdateHandler($commentID, &$arFields)
    {
        (new Service\Delete($commentID))->removeDeletedImages();

        (new Service\Upload($commentID, $arFields))->attachImages();

        Solution::updateExtendedReviewsProps($commentID);
    }

    public static function OnCommentDeleteHandler($commentID)
    {
        (new Service\Delete($commentID))->deleteImages();

        Solution::updateExtendedReviewsProps($commentID, 'delete');
    }
}
