<?php
include_once(dirname(__FILE__).'/install/demo.php');

$moduleId = 'esol.redirector';
$moduleJsId = str_replace('.', '_', $moduleId);
$pathJS = '/bitrix/js/'.$moduleId;
$pathCSS = '/bitrix/panel/'.$moduleId;
$pathLang = BX_ROOT.'/modules/'.$moduleId.'/lang/'.LANGUAGE_ID;
CModule::AddAutoloadClasses(
	$moduleId,
	array(
		'\Bitrix\EsolRedirector\DbStructure' => "lib/db_structure.php",
		'\Bitrix\EsolRedirector\RedirectTable' => "lib/redirect_table.php",
		'\Bitrix\EsolRedirector\ErrorsTable' => "lib/errors_table.php",
		'\Bitrix\EsolRedirector\RedirectSiteTable' => "lib/redirect_site_table.php",
		'\Bitrix\EsolRedirector\Events' => "lib/events.php",
		'\Bitrix\EsolRedirector\IblockRedirectWriter' => "lib/iblock_redirect_writer.php",
		'\Bitrix\EsolRedirector\ZipArchive' => "lib/zip_archive.php",
		'\Bitrix\EsolRedirector\Importer' => "lib/importer.php",
		'\Bitrix\EsolRedirector\Utils' => "lib/utils.php",
	)
);
$dbStruct = new \Bitrix\EsolRedirector\DbStructure();
$dbStruct->CheckDB();

\CJSCore::Init();
$jqueryExt = (\CJSCore::IsExtRegistered('jquery3') ? 'jquery3' : 'jquery2');
$arExtInfo = \CJSCore::getExtInfo($jqueryExt);
if(is_array($arExtInfo) && isset($arExtInfo['js']) && !is_array($arExtInfo['js']) && strlen($arExtInfo['js']) > 0 && !file_exists($_SERVER['DOCUMENT_ROOT'].$arExtInfo['js']))
{
	$arFiles = glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/jquery/jquery-'.preg_replace('/^.*(\d+)$/', '$1', $jqueryExt).'*.min.js');
	if(is_array($arFiles) && count($arFiles) > 0)
	{
		\CJSCore::RegisterExt($jqueryExt, array('js' => mb_substr(reset($arFiles), mb_strlen($_SERVER['DOCUMENT_ROOT']))));
	}
}

$arJSEsolRedirectorConfig = array(
	$moduleJsId => array(
		'js' => $pathJS.'/script.js',
		'css' => $pathCSS.'/styles.css',
		'rel' => array($jqueryExt, $moduleJsId.'_chosen'),
		'lang' => $pathLang.'/js_admin.php',
	),
	$moduleJsId.'_chosen' => array(
		'js' => $pathJS.'/chosen/chosen.jquery.min.js',
		'css' => $pathJS.'/chosen/chosen.min.css',
		'rel' => array($jqueryExt)
	),
);

foreach ($arJSEsolRedirectorConfig as $ext => $arExt) {
	\CJSCore::RegisterExt($ext, $arExt);
}
?>