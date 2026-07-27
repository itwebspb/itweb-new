<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

return [
    'js' => [
        './select2.js',
    ],
    'css' => [
        './select2.css',
        './style.css',
    ],
    'rel' => [
        'jquery3'
    ],
    'skip_core' => true,
];
