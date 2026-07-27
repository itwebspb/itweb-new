<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<input type="hidden" name="<?= $optionName; ?>" value="N">
<input
    type="checkbox"
    id="<?= $optionName; ?>"
    name="<?= $optionName; ?>"
    value="Y"
    <?= ($optionVal == 'Y' ? 'checked' : ''); ?>
    <?= $optionDisabled; ?>
>
