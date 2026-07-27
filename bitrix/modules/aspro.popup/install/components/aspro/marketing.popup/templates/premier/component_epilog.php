<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arScripts = [];
if($templateData["USE_COUNTDOWN"]){
    TSolution\Extensions::init(['countdown']);
}
?>
<?if($templateData["USE_COUNTDOWN"]):?>
    <script>typeof initCountdown === 'function' && initCountdown()</script>
<?endif;?>