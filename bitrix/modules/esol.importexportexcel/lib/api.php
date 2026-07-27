<?php
namespace Bitrix\KdaImportexcel;

class Api
{
	public function __construct()
	{
	}
	
	public function RunImport($PROFILE_ID)
	{
		$oProfile = new \CKDAImportProfile();
		if(!$oProfile->ProfileExists($PROFILE_ID)) return false;
		$oProfile->UpdateFields($PROFILE_ID, array('NEED_RUN'=>'Y'));
		return true;
	}
	
	public function GetProfilesPool()
	{
		$oProfile = new \CKDAImportProfile();
		return $oProfile->GetProfilesCronPool();
	}
	
	public function DeleteProfileFromPool($PROFILE_ID)
	{
		$oProfile = new \CKDAImportProfile();
		if(!$oProfile->ProfileExists($PROFILE_ID)) return false;
		$oProfile->UpdateFields($PROFILE_ID, array('NEED_RUN'=>'N'));
		return true;
	}
	
	public static function ImportAgent($PROFILE_ID)
	{
		$PROFILE_ID = (int)$PROFILE_ID;
		$argv[1] = $PROFILE_ID;
		ob_start();
		if(\KdaIE\Utils::GetModuleId()=='esol.importexportexcel')
		{
			include($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/esol.importexportexcel/cron_frame_import.php');
		}
		elseif(\KdaIE\Utils::GetModuleId()='kda.importexcel')
		{
			include($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/kda.importexcel/cron_frame.php');
		}
		ob_end_clean();
		
		return '\Bitrix\KdaImportexcel\Api::ImportAgent('.$PROFILE_ID.');';
	}
}