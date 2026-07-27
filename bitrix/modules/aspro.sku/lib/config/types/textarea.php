<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<textarea
    <?if (!empty($arOption['PLACEHOLDER'])) { ?>placeholder="<?= $arOption['PLACEHOLDER']; ?>"<?}?>
    <?= $optionDisabled; ?>
    rows="<?= $optionRows; ?>"
    cols="<?= $optionCols; ?>"
    name="<?= $optionName; ?>"
><?= $optionVal; ?></textarea>
