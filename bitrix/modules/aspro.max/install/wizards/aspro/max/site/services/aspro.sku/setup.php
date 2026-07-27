<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$extModuleId = 'aspro.sku';
$installExtModule = $wizard->GetVar('installSku');

$extModuleShortId = str_replace('aspro.', '', $extModuleId);
$extModuleClass = str_replace('.', '_', $extModuleId);

require realpath(__DIR__.'/../ext/setup.php');
