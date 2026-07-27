<?php

if (!include_once ($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
    throw new Exception('Error include solution constants');
}

global $USER, $pathForAjax;

if ($USER->IsAuthorized()) {
    $userId = $USER->GetID();
}

if ($userId) {
    global $USER_FIELD_MANAGER;
    $ufId = ($userId % 1000).($comment['ID'] % 1000);
    $fields = $USER_FIELD_MANAGER->GetUserFields('BLOG_COMMENT_ID', $ufId);
    $fieldValueLike = $fields['UF_LIKE_ID']['VALUE'];
    $fieldValueLike = TSolution::unserialize($fieldValueLike);

    if (isset($fieldValueLike[$userId])) {
        $valuelike = $fieldValueLike[$userId];
    } else {
        $valuelike = 'N';
    }

    $bActiveLike = $valuelike == 'Y';

    $fieldValueDisLike = $fields['UF_DISLIKE_ID']['VALUE'];
    $fieldValueDisLike = TSolution::unserialize($fieldValueDisLike);

    if (isset($fieldValueDisLike[$userId])) {
        $valuedislike = $fieldValueDisLike[$userId];
    } else {
        $valuedislike = 'N';
    }

    $bActiveDisLike = $valuedislike == 'Y';
}
?>
<span class="rating-vote line-block line-block--gap line-block--gap-20<?=$userId ? ' active' : '';?>" data-comment_id="<?=$comment['ID'];?>">
    <button type="button"
        class="rating-vote__action rating-vote__action--like btn--no-btn-appearance <?=$userId ? '' : 'disable';?> <?=$bActiveLike ? 'active' : '';?>"
        data-action="like"
        title="<?=GetMessage('LIKE');?>"
        >
        <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/reaction.svg#like', 'relative', ['WIDTH' => 12, 'HEIGHT' => 12]);?>
        <span class="rating-vote__action-result rating-vote__action-result--like"><?=intval($comment['UF_ASPRO_COM_LIKE']);?></span>
    </button>

    <button type="button"
        class="rating-vote__action rating-vote__action--dislike btn--no-btn-appearance<?=$bActiveDisLike ? ' active' : '';?><?=$userId ? '' : ' disable';?>"
        data-action="dislike"
        title="<?=GetMessage('DISLIKE');?>"
        >
        <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/reaction.svg#dislike', 'relative', ['WIDTH' => 12, 'HEIGHT' => 12]);?>
        <span class="rating-vote__action-result rating-vote__action-result--dislike"><?=intval($comment['UF_ASPRO_COM_DISLIKE']);?></span>
    </button>
</span>

<script type="text/javascript">BX?.Aspro?.Loader?.addExt?.('comment_like');</script>
