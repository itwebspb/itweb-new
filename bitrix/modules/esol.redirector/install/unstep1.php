<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(GetMessage("ESOL_RR_REMOVE_MODULE_TITLE"));
?>
<form action="<?echo htmlspecialcharsbx(\Bitrix\Main\Context::getCurrent()->getRequest()->getRequestedPage());?>" method="post">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="hidden" name="id" value="esol.redirector">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	
	<p>
		<input type="checkbox" name="remove_db_data" id="esol_remove_db_data" value="Y">
		<label for="esol_remove_db_data"><?echo Loc::getMessage("ESOL_RR_REMOVE_DB_DATA")?></label>
	</p>

	<div>&nbsp;</div>
	<input type="submit" name="inst" value="<?echo GetMessage("ESOL_RR_UNINST_DEL")?>">
</form>