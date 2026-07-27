<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$this->setFrameMode(true);

$showTitle = $arParams['SOCIAL_TITLE'] && (
    !empty($arResult['SOCIAL_VK'])
    || !empty($arResult['SOCIAL_ODNOKLASSNIKI'])
    || !empty($arResult['SOCIAL_FACEBOOK'])
    || !empty($arResult['SOCIAL_TWITTER'])
    || !empty($arResult['SOCIAL_INSTAGRAM'])
    || !empty($arResult['SOCIAL_TELEGRAM'])
    || !empty($arResult['SOCIAL_MAIL'])
    || !empty($arResult['SOCIAL_YOUTUBE'])
    || !empty($arResult['SOCIAL_VIBER'])
    || !empty($arResult['SOCIAL_WHATS'])
    || !empty($arResult['SOCIAL_WHATS_CUSTOM'])
    || !empty($arResult['SOCIAL_VIBER_CUSTOM_DESKTOP'])
    || !empty($arResult['SOCIAL_VIBER_CUSTOM_MOBILE'])
    || !empty($arResult['SOCIAL_ZEN'])
    || !empty($arResult['SOCIAL_PINTEREST'])
    || !empty($arResult['SOCIAL_SNAPCHAT'])
    || !empty($arResult['SOCIAL_TIKTOK'])
    || !empty($arResult['SOCIAL_LINKEDIN'])
    || !empty($arResult['SOCIAL_RUTUBE'])
    || !empty($arResult['SOCIAL_MAX'])
);
?>
<div class="social-icons">
<?if ($showTitle):?>
    <div class="small_title"><?=$arParams['SOCIAL_TITLE'];?></div>
<?endif;?>

<!-- noindex -->
<ul class="social-icons__list social-icons__list--top">
    <?if (!empty($arResult['SOCIAL_VK'])):?>
        <li class="social-icons__list-item vk">
            <a href="<?=$arResult['SOCIAL_VK'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_VK');?>">
                <?=TSolution::showIconSvg('vk', SITE_TEMPLATE_PATH.'/images/svg/social/social_vk.svg');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_FACEBOOK'])):?>
        <li class="social-icons__list-item facebook">
            <a href="<?=$arResult['SOCIAL_FACEBOOK'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_FACEBOOK');?>">
                <?=TSolution::showIconSvg('fb', SITE_TEMPLATE_PATH.'/images/svg/social/Facebook.svg');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_ODNOKLASSNIKI'])):?>
        <li class="social-icons__list-item odn">
            <a href="<?=$arResult['SOCIAL_ODNOKLASSNIKI'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_ODNOKLASSNIKI');?>">
                <?=TSolution::showIconSvg('odn', SITE_TEMPLATE_PATH.'/images/svg/social/Odnoklassniki.svg');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_TWITTER'])):?>
        <li class="social-icons__list-item twitter">
            <a href="<?=$arResult['SOCIAL_TWITTER'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_TWITTER');?>">
                <?=TSolution::showIconSvg('tw', SITE_TEMPLATE_PATH.'/images/svg/social/social_twitter.svg');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_INSTAGRAM'])):?>
        <li class="social-icons__list-item instagram">
            <a href="<?=$arResult['SOCIAL_INSTAGRAM'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_INSTAGRAM');?>">
                <?=TSolution::showIconSvg('inst', SITE_TEMPLATE_PATH.'/images/svg/social/Instagram.svg');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_TELEGRAM'])):?>
        <li class="social-icons__list-item telegram">
            <a href="<?=$arResult['SOCIAL_TELEGRAM'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_TELEGRAM');?>">
                <?=TSolution::showIconSvg('tlg', SITE_TEMPLATE_PATH.'/images/svg/social/social_telegram.svg');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_YOUTUBE'])):?>
        <li class="social-icons__list-item ytb">
            <a href="<?=$arResult['SOCIAL_YOUTUBE'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_YOUTUBE');?>">
                <?=TSolution::showIconSvg('ytb', SITE_TEMPLATE_PATH.'/images/svg/social/Youtube.svg');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_MAIL'])):?>
        <li class="social-icons__list-item mail">
            <a href="<?=$arResult['SOCIAL_MAIL'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_MAILRU');?>">
                <?=TSolution::showIconSvg('mail', SITE_TEMPLATE_PATH.'/images/svg/social/Mailru.svg');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_VIBER']) || !empty($arResult['SOCIAL_VIBER_CUSTOM_MOBILE'])):?>
        <?$hrefDesktop = strlen(trim($arResult['SOCIAL_VIBER_CUSTOM_DESKTOP'])) ? $arResult['SOCIAL_VIBER_CUSTOM_DESKTOP'] : 'viber://chat?number=+'.$arResult['SOCIAL_VIBER'];?>
        <li class="social-icons__list-item viber">
            <a href="<?=$hrefDesktop;?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_VIBER');?>">
                <?=TSolution::showIconSvg('vi', SITE_TEMPLATE_PATH.'/images/svg/social/Viber.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_VIBER');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_WHATS']) || !empty($arResult['SOCIAL_WHATS_CUSTOM'])):?>
        <?php
        if (strlen(trim($arResult['SOCIAL_WHATS_CUSTOM']))) {
            $whatsHref = $arResult['SOCIAL_WHATS_CUSTOM'];
        } else {
            if (defined('LANG_CHARSET') && strtolower(LANG_CHARSET) == 'windows-1251') {
                $text = iconv('windows-1251', 'utf-8', $arResult['SOCIAL_WHATS_TEXT']);
            } else {
                $text = $arResult['SOCIAL_WHATS_TEXT'];
            }
            $bWhatsText = !empty($arResult['SOCIAL_WHATS_TEXT']);
            $whatsText = $bWhatsText ? '?text='.rawurlencode($text) : '';
            $whatsHref = 'https://wa.me/'.$arResult['SOCIAL_WHATS'].$whatsText;
        }
        ?>
        <li class="social-icons__list-item whats">
            <a href="<?=$whatsHref;?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_WHATS');?>">
                <?=TSolution::showIconSvg('wh', SITE_TEMPLATE_PATH.'/images/svg/social/Whatsapp.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_WHATS');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_ZEN'])):?>
        <li class="social-icons__list-item zen">
            <a href="<?=$arResult['SOCIAL_ZEN'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_ZEN');?>">
                <?=TSolution::showIconSvg('zen', SITE_TEMPLATE_PATH.'/images/svg/social/Zen.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_ZEN');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_TIKTOK'])):?>
        <li class="social-icons__list-item tiktok">
            <a href="<?=$arResult['SOCIAL_TIKTOK'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_TIKTOK');?>">
                <?=TSolution::showIconSvg('tt', SITE_TEMPLATE_PATH.'/images/svg/social/Tiktok.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_TIKTOK');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_PINTEREST'])):?>
        <li class="social-icons__list-item pinterest">
            <a href="<?=$arResult['SOCIAL_PINTEREST'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_PINTEREST');?>">
                <?=TSolution::showIconSvg('pt', SITE_TEMPLATE_PATH.'/images/svg/social/Pinterest.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_PINTEREST');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_SNAPCHAT'])):?>
        <li class="social-icons__list-item snapchat">
            <a href="<?=$arResult['SOCIAL_SNAPCHAT'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_SNAPCHAT');?>">
                <?=TSolution::showIconSvg('sc', SITE_TEMPLATE_PATH.'/images/svg/social/Snapchat.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_SNAPCHAT');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_LINKEDIN'])):?>
        <li class="social-icons__list-item linkedin">
            <a href="<?=$arResult['SOCIAL_LINKEDIN'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_LINKEDIN');?>">
                <?=TSolution::showIconSvg('linkedin', SITE_TEMPLATE_PATH.'/images/svg/social/Linkedin.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_LINKEDIN');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_RUTUBE'])):?>
        <li class="social-icons__list-item rutube">
            <a href="<?=$arResult['SOCIAL_RUTUBE'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_RUTUBE');?>">
                <?=TSolution::showIconSvg('linkedin', SITE_TEMPLATE_PATH.'/images/svg/social/Rutube.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_RUTUBE');?>
            </a>
        </li>
    <?endif;?>
    <?if (!empty($arResult['SOCIAL_MAX'])):?>
        <li class="social-icons__list-item max">
            <a href="<?=$arResult['SOCIAL_MAX'];?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_MAX');?>">
                <?=TSolution::showIconSvg('max', SITE_TEMPLATE_PATH.'/images/svg/social/MAX.svg');?>
                <?=GetMessage('TEMPL_SOCIAL_MAX');?>
            </a>
        </li>
    <?endif;?>
    <li class="social-icons__list-item social-icons__list-item--more hidden">
        <span>...</span>
        <ul class="dropdown"></ul>
    </li>
</ul>
<script data-skip-moving="true">InitIconsGummi();</script>
<!-- /noindex -->
</div>
