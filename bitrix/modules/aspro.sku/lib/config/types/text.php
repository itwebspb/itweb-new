<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<div class="ui-ctl ui-ctl-textbox ui-ctl-sm <?=$arOption['CUSTOM_CLASS'];?>">
    <input
        type="<?= $optionType; ?>"
        class="ui-ctl-element <?=$arOption['CUSTOM_CLASS'];?>"
        <?= (isset($arOption['PARAMS']) && isset($arOption['PARAMS']['WIDTH'])) ? 'style="width:'.$arOption['PARAMS']['WIDTH'].'"' : ''; ?>
        size="<?= $optionSize; ?>"
        placeholder="<?= $arOption['PLACEHOLDER'] ?: $arOption['DEFAULT']; ?>"
        maxlength="255"
        value="<?= $optionVal; ?>"
        name="<?= $optionName; ?>"
        <?= $optionDisabled; ?>
        <?= $optionRequired; ?>
        <?= $optionCode == 'password' ? "autocomplete='off'" : ''; ?>
    >
</div>
