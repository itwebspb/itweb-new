<?php
/**
 * @var array $listIblocks
 */
use Aspro\Smartseo,
    Bitrix\Main\Localization\Loc;
Loc::loadMessages(Smartseo\General\Smartseo::getModulePath() . 'admin/index.php');
?>
<option disabled selected><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_IBLOCK') ?></option>
<?
$bNeedGroupIblocks = !empty($listIblocks) && reset($listIblocks)['IS_CATALOG'];
$bCheckGroup = false;
?>
<?if($bNeedGroupIblocks):?>
    <optgroup label="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CATALOG_GROUP_IBLOCK') ?>">
    <?$bCheckGroup = true;?>
<?endif;?>
<? foreach ($listIblocks as $iblock) : ?>
    <?if($bCheckGroup && !$iblock['IS_CATALOG']):?>
        </optgroup>
        <optgroup label="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_OTHER_GROUP_IBLOCK') ?>">
        <?$bCheckGroup = false;?>
    <?endif;?>
    <option value="<?= $iblock['ID'] ?>">[<?= $iblock['ID'] ?>] <?= $iblock['NAME'] ?></option>
<? endforeach ?>
<?if($bNeedGroupIblocks):?>
    </optgroup>
<?endif;?>

