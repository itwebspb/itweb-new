<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$templateData['HAS_PROPS'] = !empty($arResult['PROPS']);
if (!$templateData['HAS_PROPS']) {
    return;
}
?>

<!--<?=$arParams['COMPONENT_MARKER'];?>-->
<div hidden>
    <?$component->GetParent()->showPropsHtml($arResult['PROPS']);?>
</div>
<!--/<?=$arParams['COMPONENT_MARKER'];?>-->
