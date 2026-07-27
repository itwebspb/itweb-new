<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<div class="adm-input-wrap adm-input-wrap-calendar">
    <input
        class="adm-input adm-input-calendar"
        type="text"
        name="<?=$optionName;?>"
        size="20"
        value="<?= $optionVal; ?>"
        placeholder="<?= $arOption['PLACEHOLDER'] ?: $arOption['DEFAULT']; ?>"
    >
    <span
        class="adm-calendar-icon"
        title="<?=GetMessage("admin_lib_calend_title");?>"
        onclick="BX.calendar({node:this, field:'<?=$optionName;?>', form: '', bTime: true, bHideTime: false});"
    ></span>
</div>
