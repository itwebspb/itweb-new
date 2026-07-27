<?php

namespace Aspro\Sku\Config;

use Aspro\Sku\General;
use Aspro\Sku\Orm\RulesSku;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class UI
{
    public const ALLOWED_TYPES = [
        'checkbox',
        'datalist',
        'date',
        'multiselectbox',
        'radio',
        'readonly',
        'selectbox',
        'text',
        'textarea',
    ];

    public static function saveOptions($arTabs)
    {
        foreach ($arTabs as $arTab) {
            if (!empty($arTab['SUB_TABS'])) {
                foreach ($arTab['SUB_TABS'] as $arSubTab) {
                    self::saveTabOption($arSubTab);
                }
            } else {
                self::saveTabOption($arTab);
            }
        }
    }

    public static function saveTabOption($arTab)
    {
        $optionsSiteID = $arTab['SITE_ID'];

        foreach ($arTab['OPTIONS'] as $blockCode => $arBlock) {
            foreach ($arBlock['OPTIONS'] as $optionCode => $arOption) {
                $optionType = $arOption['TYPE'];
                $optionName = self::mkOptionName($optionCode, $arTab);

                $newVal = $_REQUEST[$optionName] ?? '';

                if ($optionType === 'checkbox') {
                    if (!strlen($newVal) || $newVal != 'Y') {
                        $newVal = 'N';
                    }
                } elseif (in_array($optionType, ['datalist', 'multiselectbox'])) {
                    $newVal = implode(',', $newVal ?: []);
                }

                if (is_array($newVal)) {
                    $newVal = implode(',', $newVal ?: []);
                }

                if ($optionType !== 'file') {
                    $arTab['OPTIONS'][$optionCode] = $newVal;
                }

                Option::set(General::moduleId, $optionCode, htmlspecialcharsbx($newVal), $optionsSiteID ?: '');
            }
        }

        \CBitrixComponent::clearComponentCache('bitrix:catalog.element', $optionsSiteID);
    }

    public static function showOptionsRow($arTab)
    {
        if (!$arTab['OPTIONS']) {
            return;
        }

        foreach ($arTab['OPTIONS'] as $blockCode => $arBlock) {
            self::showOptionRow($blockCode, $arBlock, $arTab);
        }

        self::loadUiHint();
    }

    public static function showOptionRow($blockCode, $arBlock, $arTab)
    {
        ?>
        <?if (strlen($arBlock['TITLE'] ?? '')):?>
            <tr class="heading" data-blockcode="<?=$blockCode;?>">
                <td colspan="2"><?=$arBlock['TITLE'];?></td>
            </tr>
        <?endif;?>

        <?foreach ($arBlock['OPTIONS'] as $optionCode => $arOption):?>
            <tr data-optioncode="<?=$optionCode;?>" <?=$arOption['HIDDEN'] ? 'hidden' : '';?>>
                <?=self::showOptionValue($optionCode, $arOption, $arTab);?>
            </tr>
        <?endforeach;?>
        <?php
    }

    public static function showOptionValue($optionCode, $arOption, $arTab)
    {
        $optionType = $arOption['TYPE'];
        $optionList = $arOption['LIST'];

        $optionName = self::mkOptionName($optionCode, $arTab);
        $optionTitle = self::mkOptionTitle($arOption);
        $optionVal = self::mkOptionValue($optionCode, $arOption, $arTab);

        $optionSize = $arOption['SIZE'];
        $optionCols = $arOption['COLS'];
        $optionRows = $arOption['ROWS'];

        $optionDisabled = array_key_exists('DISABLED', $arOption) && $arOption['DISABLED'] == 'Y' ? 'disabled' : '';
        $optionRequired = $arOption['REQUIRED'] ? 'required' : '';
        ?>
        <?if ($optionType == 'note'):?>
            <td colspan="2" align="center">
                <div class="notes-block" data-option_code="<?=$optionCode;?>">
                    <div align="center">
                        <?=BeginNote('align="center" name="'.$optionName.'"');?>
                            <?=$optionTitle ? $optionTitle : $arOption['NOTE'];?>
                        <?=EndNote();?>
                    </div>
                </div>
            </td>
            <?return;?>
        <?endif;?>

        <?if (!$arOption['HIDE_TITLE']):?>
            <td class="<?=in_array($optionType, ['multiselectbox', 'textarea', 'statictext', 'statichtml']) ? 'adm-detail-valign-top' : '';?>" width="50%">
                <?if ($arOption['HINT']):?><span data-hint="<?=$arOption['HINT'];?>"></span><?endif;?>
                <?if ($optionType == 'checkbox'):?>
                    <label for="<?=$optionName;?>"><?=$optionTitle;?></label>
                <?else:?>
                    <?=$optionTitle;?>
                <?endif;?>
            </td>

            <td width="50%">
        <?endif;?>

            <?if ($optionType === 'group'):?>
                <?foreach ($arOption['OPTIONS'] as $subOptionCode => $arSubOption):?>
                    <div class="ui-ctl-inline" data-optioncode="<?=$subOptionCode;?>">
                        <?=self::showOptionValue($subOptionCode, $arSubOption, $arTab);?>
                    </div>
                <?endforeach;?>
            <?endif;?>

            <?php
            if (in_array($optionType, self::ALLOWED_TYPES)) {
                include "types/{$optionType}.php";
            } elseif ($arOption['CUSTOM_TYPE']) {
                $customType = str_replace(['.', '/'], '', $arOption['CUSTOM_TYPE']);

                $fp = "types/{$optionType}/{$customType}.php";
                if (file_exists(__DIR__.'/'.$fp)) {
                    include $fp;
                }
            } else {
                echo $optionVal;
            }
            ?>

        <?if (!$arOption['HIDE_TITLE']):?>
            </td>
        <?endif;?>
        <?php
    }

    private static function mkOptionName($code, $arTab)
    {
        if ($arTab['TYPE'] === 'ORM' && $code !== 'ID') {
            return htmlspecialcharsbx('FIELDS['.$code.']');
        }

        $nameList = [$code];

        if ($arTab['SITE_ID']) {
            $nameList[] = $arTab['SITE_ID'];
        }

        return htmlspecialcharsbx(implode('_', $nameList));
    }

    private static function mkOptionTitle($arOption)
    {
        if (!$arOption['TITLE']) {
            return '';
        }

        $title = $arOption['TITLE'].':';

        if ($arOption['REQUIRED']) {
            $title = "<b>{$title}</b>";
        }

        return $title;
    }

    private static function mkOptionValue($code, $arOption, $arTab)
    {
        $defaultValue = $arOption['DEFAULT'];

        if ($arTab['TYPE'] === 'ORM') {
            return is_array($arOption['VALUE'])
                ? $arOption['VALUE']
                : htmlspecialcharsbx($arOption['VALUE'] ?? $defaultValue);
        }

        return htmlspecialcharsbx(Option::get(General::moduleId, $code, $defaultValue, $arTab['SITE_ID'] ?: ''));
    }

    public static function loadUiSelect2(string $optionCode, ?string $placeholder = '', array $options = [])
    {
        \Bitrix\Main\UI\Extension::load('aspro.sku.vendor.select2');
        ?>
        <script>
            BX.ready(() => {
                const config = <?=$options ? \CUtil::PhpToJsObject($options, false, false, true) : '{}';?>;

                config.placeholder = '<?=$placeholder;?>';
                config.templateSelection = (state) => state.text.trim().replace(/^[\.\s]+/, "");
                config.language = {
                    searching: () => "<?=General::GetMessage('SELECT2_SEARCHING');?>",
                    inputTooShort: () => "<?=General::GetMessage('SELECT2_TOO_SHORT_3');?>",
                    noResults: () => "<?=General::GetMessage('SELECT2_ERROR_LOADING');?>",
                    errorLoading: () => "<?=General::GetMessage('SELECT2_ERROR_LOADING');?>",
                    removeItem: () => "<?=General::GetMessage('SELECT2_REMOVE_ITEM');?>",
                };

                $('[data-optioncode="<?=$optionCode;?>"] select').select2(config).on("select2:select", (event) => {
                    const changeEvent = new Event("change", {
                        bubbles: true,
                        cancelable: true,
                    });
                    event.currentTarget.dispatchEvent(changeEvent);
                });
            })
        </script>
        <?php
    }

    private static function loadUiHint()
    {
        \Bitrix\Main\UI\Extension::load('ui.hint');
        ?>
        <script>
            BX.ready(function() {
                BX.UI.Hint.init(BX('adm-detail-content-item-block'));
            })
        </script>
        <?php
    }
}
