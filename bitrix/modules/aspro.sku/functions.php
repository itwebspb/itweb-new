<?
CModule::IncludeModule("main");
CModule::IncludeModule("iblock");

set_time_limit(0);

if (!function_exists("ClearAllSitesCacheComponents")) {
	function ClearAllSitesCacheComponents($arComponentsNames)
	{
		if ($arComponentsNames && is_array($arComponentsNames)) {
			global $CACHE_MANAGER;
			$arSites = array();
			$rsSites = CSite::GetList($by = "sort", $order = "desc", array("ACTIVE" => "Y"));
			while ($arSite = $rsSites->Fetch()) {
				$arSites[] = $arSite;
			}
			foreach ($arComponentsNames as $componentName) {
				foreach ($arSites as $arSite) {
					CBitrixComponent::clearComponentCache($componentName, $arSite["ID"]);
				}
			}
		}
	}
}

if (!function_exists("ClearAllSitesCacheDirs")) {
	function ClearAllSitesCacheDirs($arDirs)
	{
		if ($arDirs && is_array($arDirs)) {
			foreach ($arDirs as $dir) {
				$obCache = new CPHPCache();
				$obCache->CleanDir("", $dir);
			}
		}
	}
}

if (!function_exists("GetIBlocks")) {
	function GetIBlocks()
	{
		$arRes = array();
		$dbRes = CIBlock::GetList(array(), array("ACTIVE" => "Y"));
		while ($item = $dbRes->Fetch()) {
			$dbIBlockSites = CIBlock::GetSite($item['ID']);
			while($arIBlockSite = $dbIBlockSites->Fetch()){
				$arRes[$arIBlockSite["SITE_ID"]][$item["IBLOCK_TYPE_ID"]][$item["CODE"]][] = $item["ID"];
			}
		}

		return $arRes;
	}
}

if (!function_exists("GetSites")) {
	function GetSites()
	{
		$arRes = array();
		$dbRes = CSite::GetList($by = "sort", $order = "desc", array("ACTIVE" => "Y"));
		while ($item = $dbRes->Fetch()) {
			$arRes[$item["LID"]] = $item;
		}
		return $arRes;
	}
}

if (!function_exists("GetCurVersion")) {
	function GetCurVersion($versionFile)
	{
		$ver = false;
		if (file_exists($versionFile)) {
			$arModuleVersion = array();
			include($versionFile);
			$ver = trim($arModuleVersion["VERSION"]);
		}
		return $ver;
	}
}

if (!function_exists("CreateBakFile")) {
	function CreateBakFile($file, $curVersion = CURRENT_VERSION)
	{
		$file = trim($file);
		if (file_exists($file)) {
			$arPath = pathinfo($file);
			$backFile = $arPath['dirname'] . '/_' . $arPath['basename'] . '.back' . $curVersion;
			if (!file_exists($backFile)) {
				@copy($file, $backFile);
			}
		}
	}
}

if(!function_exists('RemoveOldBakFiles')){
	function RemoveOldBakFiles(){
		$arDirs = $arFiles = array();

		foreach(
			$arExclude = array(
				'bitrix',
				'local',
				'upload',
				'webp-copy',
				'cgi',
				'cgi-bin',
			) as $dir){
			$arDirExclude[] = $_SERVER['DOCUMENT_ROOT'].'/'.$dir;
		}

		// public
		if($arSites = GetSites()){
			foreach($arSites as $siteID => $arSite){
				$arSite['DIR'] = str_replace('//', '/', '/'.$arSite['DIR']);
				if(!strlen($arSite['DOC_ROOT'])){
					$arSite['DOC_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
				}
				$arSite['DOC_ROOT'] = str_replace('//', '/', $arSite['DOC_ROOT'].'/');
				$siteDir = str_replace('//', '/', $arSite['DOC_ROOT'].$arSite['DIR']);

				if($arPublicDirs = glob($siteDir.'*', GLOB_ONLYDIR|GLOB_NOSORT)){
					foreach($arPublicDirs as $dir){
						foreach($arExclude as $exclude){
							if(strpos($dir, '/'.$exclude) !== false){
								continue 2;
							}
						}

						$arDirs[] = str_replace('//', '/', $dir.'/');
					}
				}
			}

			$i = 0;
			while($arDirs && ++$i < 10000){
				$dir = array_pop($arDirs);
				$arFiles = array_merge($arFiles, (array)glob($dir.'_*.back*', GLOB_NOSORT));
				foreach((array)glob($dir.'*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){
					if(
						strlen($dir)
					){
						foreach($arExclude as $exclude){
							if(strpos($dir, '/'.$exclude) !== false){
								continue 2;
							}
						}

						$arDirs[] = str_replace('//', '/', $dir.'/');
					}
				}
			}
		}

		$arDirs = array();

		// aspro components
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/')){
			if($arComponents = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.PARTNER_NAME.'*', 0)){
				foreach($arComponents as $componentPath){
					$arDirs[] = str_replace('//', '/', $componentPath.'/');
				}
			}
		}
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/local/components/')){
			if($arComponents = glob($_SERVER['DOCUMENT_ROOT'].'/local/components/'.PARTNER_NAME.'*', 0)){
				foreach($arComponents as $componentPath){
					$arDirs[] = str_replace('//', '/', $componentPath.'/');
				}
			}
		}

		// aspro modules
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/')){
			if($arModules = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.PARTNER_NAME.'*', 0)){
				foreach($arModules as $modulePath){
					$arDirs[] = str_replace('//', '/', $modulePath.'/');
				}
			}
		}
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/local/modules/')){
			if($arModules = glob($_SERVER['DOCUMENT_ROOT'].'/local/modules/'.PARTNER_NAME.'*', 0)){
				foreach($arModules as $modulePath){
					$arDirs[] = str_replace('//', '/', $modulePath.'/');
				}
			}
		}

		// aspro js
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/')){
			if($arJs = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.MODULE_NAME.'*', 0)){
				foreach($arJs as $jsPath){
					$arDirs[] = str_replace('//', '/', $jsPath.'/');
				}
			}
		}

		// aspro css
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/css/')){
			if($arCss = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/css/'.MODULE_NAME.'*', 0)){
				foreach($arCss as $cssPath){
					$arDirs[] = str_replace('//', '/', $cssPath.'/');
				}
			}
		}

		$i = 0;
		while($arDirs && ++$i < 10000){
			$popdir = array_pop($arDirs);
			$arFiles = array_merge($arFiles, (array)glob($popdir.'_*.back*', GLOB_NOSORT));
			foreach((array)glob($popdir.'{,.}*', GLOB_ONLYDIR|GLOB_NOSORT|GLOB_BRACE) as $dir){
				if(
					strlen($dir) &&
					!in_array($dir, array($popdir.'.', $popdir.'..')) &&
					!in_array($dir, $arDirExclude) &&
					(
						strpos($dir, PARTNER_NAME) !== false ||
						strpos($dir, '/templates/') !== false
					)
				){
					$arDirs[] = str_replace('//', '/', $dir.'/');
				}
			}
		}

		if($arFiles){
			foreach($arFiles as $file){
				if(file_exists($file) && !is_dir($file)){
					if(time() - filemtime($file) >= 1209600){ // 14 days
						@unlink($file);
					}
				}
			}
		}
	}
}

if (!function_exists('GetDBcharset')) {
    function GetDBcharset()
    {
        $sql = 'SHOW VARIABLES LIKE "character_set_database";';
        if (method_exists('\Bitrix\Main\Application', 'getConnection')) {
            $db = Bitrix\Main\Application::getConnection();
            $arResult = $db->query($sql)->fetch();

            return $arResult['Value'];
        } elseif (defined('BX_USE_MYSQLI') && BX_USE_MYSQLI == true) {
            if ($result = @mysqli_query($sql)) {
                $arResult = mysql_fetch_row($result);

                return $arResult[1];
            }
        } elseif ($result = @mysql_query($sql)) {
            $arResult = mysql_fetch_row($result);

            return $arResult[1];
        }

        return false;
    }
}

if (!function_exists('GetMes')) {
    function GetMes($str)
    {
        if (method_exists('\Bitrix\Main\Text\Encoding', 'convertEncodingToCurrent')) {
            return \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($str);
        }

        static $isUTF8;
        if ($isUTF8 === null) {
            if (method_exists('\Bitrix\Main\Application', 'isUtfMode')) {
                $isUTF8 = \Bitrix\Main\Application::isUtfMode();
            } else {
                $isUTF8 = stripos(GetDBcharset(), 'utf8') !== false;
            }
        }

        return $isUTF8 ? iconv('CP1251', 'UTF-8', $str) : $str;
    }
}

if (!function_exists("UpdaterLog")) {
	function UpdaterLog($str)
	{
		static $fLOG;
		if ($bFirst = !$fLOG) {
			$fLOG = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . MODULE_NAME . '/updaterlog.txt';
		}
		if (is_array($str)) {
			$str = print_r($str, 1);
		}
		@file_put_contents($fLOG, ($bFirst ? PHP_EOL : '') . date("d.m.Y H:i:s", time()) . ' ' . $str . PHP_EOL, FILE_APPEND);
	}
}
