<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;
if(!CModule::IncludeModule("aspro.max")) return;

if(!defined("WIZARD_SITE_ID")) return;
if(!defined("WIZARD_SITE_DIR")) return;
if(!defined("WIZARD_SITE_PATH")) return;
if(!defined("WIZARD_TEMPLATE_ID")) return;
if(!defined("WIZARD_TEMPLATE_ABSOLUTE_PATH")) return;
if(!defined("WIZARD_THEME_ID")) return;

// iblocks ids
$catalogIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_catalog"][0];
$banners_catalogIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_adv"]["aspro_max_banners_catalog"][0];
$skuIblockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_sku"][0];
$bannersOnTopIblockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_adv"]["aspro_max_banners"][0];
$landingIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]['aspro_max_catalog']['aspro_max_landing'][0];

if ($bannersOnTopIblockID) {
    $propertyCode = "BANNER_OPACITY";
    $arUserOptionsForm = CUserOptions::GetOption('form', 'form_element_'.$bannersOnTopIblockID, []);
    $strOptionTab = '';

    $existingProp = CIBlockProperty::GetList([], [
        "IBLOCK_ID" => $bannersOnTopIblockID,
        "CODE" => $propertyCode
    ])->Fetch();

    if (!$existingProp) {
        $property = new CIBlockProperty();
        $propertyId = $property->Add([
            "NAME" => GetMessage("DARKENING_LIGHTENING"),
            "ACTIVE" => "Y",
            "SORT" => 500,
            "CODE" => $propertyCode,
            "IBLOCK_ID" => $bannersOnTopIblockID,
            "PROPERTY_TYPE" => "L",
            "LIST_TYPE" => "C",
            "MULTIPLE" => "N",
        ]);

        if ($propertyId) {
            $strOptionTab .= ',--PROPERTY_'.$propertyId.'--#--'.GetMessage("DARKENING_LIGHTENING").'--';

            $enum = new CIBlockPropertyEnum();
            $enum->Add([
                "PROPERTY_ID" => $propertyId,
                "VALUE" => "Y",
                "XML_ID" => "Y",
                "DEF" => "N",
                "SORT" => 100,
            ]);
        }

        if ($strOptionTab && isset($arUserOptionsForm['tabs'])) {
            $matches = [];
            $subject = '/(--Aspro--.*?);/s';
            preg_match($subject, $arUserOptionsForm['tabs'], $matches);

            if ($matches[0]) {
                $patternNewProperty = $matches[1].$strOptionTab;
                $arUserOptionsForm['tabs'] = str_replace($matches[1], $patternNewProperty, $arUserOptionsForm['tabs']);
            } else {
                $matches = [];
                preg_match_all('/\bedit(\d)\b/', $arUserOptionsForm['tabs'], $matches, false);
                sort($matches[1]);
                $editNumber = array_pop($matches[1]);

                $addPropForm = 'edit'.($editNumber + 1).'--#--Aspro--'.$strOptionTab.';--';
                $arUserOptionsForm['tabs'] .= $addPropForm;
            }

            $arUserOptionsForm = CUserOptions::SetOption('form', 'form_element_'.$bannersOnTopIblockID, $arUserOptionsForm);
         }
    }
}

if ($catalogIBlockID) {
	$ib = new CIBlock;
	$ib->Update(
		$catalogIBlockID,
		[
			'INDEX_ELEMENT' => 'Y',
			'INDEX_SECTION' => 'Y',
		]
	);
}

if ($banners_catalogIBlockID) {
	$ib = new CIBlock;
	$ib->Update(
		$banners_catalogIBlockID,
		[
			'INDEX_ELEMENT' => 'N',
			'INDEX_SECTION' => 'N',
		]
	);
}

//set option for props group
$strOptionGroup = "";
if($catalogIBlockID){
	$strOptionGroup = $catalogIBlockID;
	if($skuIblockID){
		$strOptionGroup = $catalogIBlockID . "," . $skuIblockID;
	}
}
\Bitrix\Main\Config\Option::set("aspro.max", "ASPRO_PROPS_GROUP_IBLOCK", $strOptionGroup, WIZARD_SITE_ID);

DeleteDirFilesEx(str_replace('//', '/', WIZARD_SITE_PATH.'/form/'));

// marketings popups link to new site
if(\Bitrix\Main\Loader::includeModule('aspro.popup')){
	$marketingType = 'aspro_popup_adv';
	$marketingCode = 'aspro_popup_marketings';
	$curMarketingIblock = CMaxCache::$arIBlocks[WIZARD_SITE_ID][$marketingType][$marketingCode][0];

	if(!$curMarketingIblock){
		$rsIBlock = CIBlock::GetList(array(), array("CODE" => $marketingCode, "TYPE" => $marketingType, "ACTIVE" => "Y"));
		if ($arIBlock = $rsIBlock->Fetch()) {
			$iblockID = $arIBlock["ID"];
			// attach iblock to site
			$arSites = array();
			$db_res = CIBlock::GetSite($iblockID);
			while ($res = $db_res->Fetch())
				$arSites[] = $res["LID"];
			if (!in_array(WIZARD_SITE_ID, $arSites)){
				$arSites[] = WIZARD_SITE_ID;
				$iblock = new CIBlock;
				$iblock->Update($iblockID, array("LID" => $arSites));
			}
		}
	}
}

if ($landingIBlockID) {
    $propertyCode = "LINKED_BUTTON";
    $arUserOptionsForm = CUserOptions::GetOption('form', 'form_element_'.$landingIBlockID, []);
    $strOptionTab = '';

    $existingProp = CIBlockProperty::GetList([], [
        "IBLOCK_ID" => $landingIBlockID,
        "CODE" => $propertyCode
    ])->Fetch();

    if (!$existingProp) {
        $property = new CIBlockProperty();
        $propertyId = $property->Add([
            "NAME" => GetMessage("LINKED_BUTTON"),
            "ACTIVE" => "Y",
            "SORT" => 500,
            "CODE" => $propertyCode,
            "IBLOCK_ID" => $landingIBlockID,
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'SAsproMaxTextWithLink',
            "MULTIPLE" => "N",
        ]);

        if ($propertyId) {
            $strOptionTab .= ',--PROPERTY_'.$propertyId.'--#--'.GetMessage("LINKED_BUTTON").'--';

            $enum = new CIBlockPropertyEnum();
            $enum->Add([
                "PROPERTY_ID" => $propertyId,
                "VALUE" => "Y",
                "XML_ID" => "Y",
                "DEF" => "N",
                "SORT" => 100,
            ]);
        }

        if ($strOptionTab && isset($arUserOptionsForm['tabs'])) {
            $matches = [];
            $tabName = GetMessage("ASPRO_TAB_NAME");
            $subject = '/(--'.$tabName.'--.*?);/su';
            preg_match($subject, $arUserOptionsForm['tabs'], $matches);

            if ($matches[0]) {
                $patternNewProperty = $matches[1].$strOptionTab;
                $arUserOptionsForm['tabs'] = str_replace($matches[1], $patternNewProperty, $arUserOptionsForm['tabs']);
            } else {
                $matches = [];
                preg_match_all('/\bedit(\d)\b/', $arUserOptionsForm['tabs'], $matches, false);
                sort($matches[1]);
                $editNumber = array_pop($matches[1]);

                $addPropForm = 'edit'.($editNumber + 1).'--#--'.$tabName.'--'.$strOptionTab.';--';
                $arUserOptionsForm['tabs'] .= $addPropForm;
            }

            $arUserOptionsForm = CUserOptions::SetOption('form', 'form_element_'.$landingIBlockID, $arUserOptionsForm);
         }
    }
}

if ($catalogIBlockID) {
    $propertyCode = "BRAND";

    $property = CIBlockProperty::GetList([], [
        "IBLOCK_ID" => $catalogIBlockID,
        "CODE" => $propertyCode
    ])->Fetch();

    if ($property) {
        \Bitrix\Iblock\Model\PropertyFeature::setFeatures($property["ID"], [      
            ["FEATURE_ID"=>"DETAIL_PAGE_SHOW", "IS_ENABLED" => "Y", "MODULE_ID" => "iblock"], 
            ["FEATURE_ID"=>"LIST_PAGE_SHOW", "IS_ENABLED" => "Y", "MODULE_ID" => "iblock"]  
        ]);
    }
}
?>
