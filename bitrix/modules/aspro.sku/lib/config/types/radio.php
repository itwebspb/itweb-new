<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    exit();
}?>


<?foreach ($optionList as $listKey => $listOptions):?>
    <?php
    $isChecked = $listKey == $optionVal;
    ?>
    <div class="aspro-sku__form-control">
        <input id="<?=$optionName;?>_<?=$listKey;?>"
            type="radio"
            name="<?=$optionName;?>"
            value="<?=$listKey;?>"
            <?=$isChecked ? ' checked' : '';?>
            <?=$optionDisabled;?>
            >
        <label for="<?=$optionName;?>_<?=$listKey;?>"><?=$listOptions;?></label>
    </div>
<?endforeach;?>
