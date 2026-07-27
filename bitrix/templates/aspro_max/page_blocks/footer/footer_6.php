<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

global $arTheme;
?>

<div class="footer-v5">
    <div class="footer-inner shorten light">
        <div class="footer_top">
            <div class="maxwidth-theme">
                <div class="row">
                    <div class="wrapper col-md-5">
                        <div class="first_bottom_menu">
                            <?$APPLICATION->IncludeComponent('bitrix:main.include', '.default',
                                [
                                    'COMPONENT_TEMPLATE' => '.default',
                                    'PATH' => SITE_DIR.'include/footer/menu/menu_bottom5.php',
                                    'AREA_FILE_SHOW' => 'file',
                                    'AREA_FILE_SUFFIX' => '',
                                    'AREA_FILE_RECURSIVE' => 'Y',
                                    'EDIT_TEMPLATE' => 'include_area.php',
                                ],
                                false, ['HIDE_ICONS' => 'Y']
                            );?>
                        </div>

                        <div class="social-block">
                            <?$APPLICATION->IncludeComponent('bitrix:main.include', '.default',
                                [
                                    'COMPONENT_TEMPLATE' => '.default',
                                    'PATH' => SITE_DIR.'include/footer/social.info.php',
                                    'AREA_FILE_SHOW' => 'file',
                                    'AREA_FILE_SUFFIX' => '',
                                    'AREA_FILE_RECURSIVE' => 'Y',
                                    'EDIT_TEMPLATE' => 'include_area.php',
                                ],
                                false, ['HIDE_ICONS' => 'Y']
                            );?>
                        </div>
                    </div>

                    <div class="col-md-3 contact-block">
                        <div class="info">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="phone blocks">
                                        <div class="inline-block">
                                            <?TSolution::ShowHeaderPhones('white sm', true);?>
                                        </div>
                                        <?$callbackExploded = explode(',', $arTheme['SHOW_CALLBACK']['VALUE']);?>
                                        <?if (in_array('FOOTER', $callbackExploded)):?>
                                            <div class="inline-block callback_wrap">
                                                <span class="callback-block animate-load colored" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback"><?=GetMessage('CALLBACK');?></span>
                                            </div>
                                        <?endif;?>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12">
                                    <?=TSolution::showEmail('email blocks');?>
                                </div>

                                <div class="col-md-12 col-sm-12">
                                    <?=TSolution::showAddress('address blocks');?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-md-offset-1">
                        <div class="info">
                            <?if (\Bitrix\Main\Loader::includeModule('subscribe') && $arTheme['HIDE_SUBSCRIBE']['VALUE'] != 'Y'):?>
                                <div class="subscribe_button">
                                    <span class="btn" data-event="jqm" data-param-id="subscribe" data-param-type="subscribe" data-name="subscribe"><?=GetMessage('SUBSCRIBE_TITLE');?>

                                    <?=TSolution::showIconSvg('subscribe', SITE_TEMPLATE_PATH.'/images/svg/subscribe_small_footer.svg');?></span>
                                </div>
                            <?endif;?>

                            <div>
                                <?TSolution\Template\Common\IncludeAreas::showFooterPolicy();?>
                            </div>
                        </div>
                    </div>
                </div>

                <?TSolution\Template\Common\IncludeAreas::showFooterUserBlock('mt mt--32');?>
            </div>
        </div>

        <div class="footer_bottom">
            <div class="maxwidth-theme">
                <div class="footer-bottom__items-wrapper">
                    <div class="footer-bottom__item copy font_xs">
                        <?$APPLICATION->IncludeFile(SITE_DIR.'include/footer/copy/copyright.php', [], [
                            'MODE' => 'php',
                            'NAME' => 'Copyright',
                            'TEMPLATE' => 'include_area.php',
                        ]);?>
                    </div>

                    <div id="bx-composite-banner"></div>

                    <div class="footer-bottom__item pays">
                        <?$APPLICATION->IncludeFile(SITE_DIR.'include/footer/copy/pay_system_icons.php', [], [
                            'MODE' => 'php',
                            'NAME' => 'onfidentiality',
                            'TEMPLATE' => 'include_area.php',
                        ]);?>
                    </div>

                    <?TSolution\Functions::showDeveloperBlock('light');?>
                </div>
            </div>
        </div>
    </div>
</div>
