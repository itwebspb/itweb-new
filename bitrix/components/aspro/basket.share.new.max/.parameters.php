<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
Loc::loadMessages(__FILE__);

$arSites = ['' => Loc::getMessage('BSN_P_SITE_ID_EMPTY')];
$dbRes = CSite::GetList($by = 'sort', $order = 'desc', ['ACTIVE' => 'Y']);
while ($arSite = $dbRes->Fetch()) {
    $arSites[$arSite['LID']] = '['.$arSite['LID'].'] '.$arSite['NAME'];
}

$arComponentParameters = [
    'GROUPS' => [
        'VISUAL' => [
            'NAME' => Loc::getMessage('BSN_G_VISUAL_TITLE'),
            'SORT' => '500',
        ],
        'MESSAGES' => [
            'NAME' => Loc::getMessage('BSN_G_MESSAGES_TITLE'),
            'SORT' => '800',
        ],
    ],
    'PARAMETERS' => [
        'SET_PAGE_TITLE' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('BSN_P_SET_PAGE_TITLE_TITLE'),
            'TYPE' => 'CHECKBOX',
            'ADDITIONAL_VALUES' => 'N',
            'DEFAULT' => 'Y',
            'REFRESH' => 'N',
        ],
        'SITE_ID' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('BSN_P_SITE_ID_TITLE'),
            'TYPE' => 'LIST',
            'VALUES' => $arSites,
            'DEFAULT' => '',
        ],
        'USER_ID' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('BSN_P_USER_ID_TITLE'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'PATH_TO_SHARE_BASKET' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('BSN_P_PATH_TO_SHARE_BASKET_TITLE'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'SHOW_SHARE_SOCIALS' => [
            'PARENT' => 'VISUAL',
            'NAME' => Loc::getMessage('BSN_P_SHOW_SHARE_SOCIALS_TITLE'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
            'REFRESH' => 'Y',
        ],
    ],
];

if ($arCurrentValues['SHOW_SHARE_SOCIALS'] !== 'N') {
    $arComponentParameters['PARAMETERS'] = array_merge(
        $arComponentParameters['PARAMETERS'],
        [
            'SHARE_SOCIALS' => [
                'PARENT' => 'VISUAL',
                'NAME' => Loc::getMessage('BSN_P_SHARE_SOCIALS_TITLE'),
                'TYPE' => 'LIST',
                'MULTIPLE' => 'Y',
                'VALUES' => [
                    'VKONTAKTE' => Loc::getMessage('BSN_P_SHARE_SOCIALS_VKONTAKTE'),
                    'FACEBOOK' => Loc::getMessage('BSN_P_SHARE_SOCIALS_FACEBOOK'),
                    'ODNOKLASSNIKI' => Loc::getMessage('BSN_P_SHARE_SOCIALS_ODNOKLASSNIKI'),
                    'MOIMIR' => Loc::getMessage('BSN_P_SHARE_SOCIALS_MOIMIR'),
                    'TWITTER' => Loc::getMessage('BSN_P_SHARE_SOCIALS_TWITTER'),
                    'VIBER' => Loc::getMessage('BSN_P_SHARE_SOCIALS_VIBER'),
                    'WHATSAPP' => Loc::getMessage('BSN_P_SHARE_SOCIALS_WHATSAPP'),
                    'SKYPE' => Loc::getMessage('BSN_P_SHARE_SOCIALS_SKYPE'),
                    'TELEGRAM' => Loc::getMessage('BSN_P_SHARE_SOCIALS_TELEGRAM'),
                    'MAX' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_MAX'),
                ],
                'DEFAULT' => [
                    'VKONTAKTE',
                    'ODNOKLASSNIKI',
                    'MAX',
                ],
            ],
        ]
    );
}

$arComponentParameters['PARAMETERS'] = array_merge(
    $arComponentParameters['PARAMETERS'],
    [
        'SHOW_QRCODE' => [
            'PARENT' => 'VISUAL',
            'NAME' => Loc::getMessage('BSN_P_SHOW_QRCODE_TITLE'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
            'REFRESH' => 'N',
        ],
        'USE_CUSTOM_MESSAGES' => [
            'PARENT' => 'MESSAGES',
            'NAME' => Loc::getMessage('BSN_P_USE_CUSTOM_MESSAGES_TITLE'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
            'REFRESH' => 'Y',
        ],
    ]
);

if ($arCurrentValues['USE_CUSTOM_MESSAGES'] === 'Y') {
    $arComponentParameters['PARAMETERS'] = array_merge(
        $arComponentParameters['PARAMETERS'],
        [
            'MESS_TITLE' => [
                'PARENT' => 'MESSAGES',
                'NAME' => Loc::getMessage('BSN_P_MESS_TITLE_TITLE'),
                'TYPE' => 'STRING',
                'DEFAULT' => Loc::getMessage('BSN_P_MESS_TITLE_DEFAULT'),
            ],
            'MESS_URL_COPY_HINT' => [
                'PARENT' => 'MESSAGES',
                'NAME' => Loc::getMessage('BSN_P_MESS_URL_COPY_HINT_TITLE'),
                'TYPE' => 'STRING',
                'DEFAULT' => Loc::getMessage('BSN_P_MESS_URL_COPY_HINT_DEFAULT'),
            ],
            'MESS_URL_COPIED_HINT' => [
                'PARENT' => 'MESSAGES',
                'NAME' => Loc::getMessage('BSN_P_MESS_URL_COPIED_HINT_TITLE'),
                'TYPE' => 'STRING',
                'DEFAULT' => Loc::getMessage('BSN_P_MESS_URL_COPIED_HINT_DEFAULT'),
            ],
            'MESS_URL_COPY_ERROR_HINT' => [
                'PARENT' => 'MESSAGES',
                'NAME' => Loc::getMessage('BSN_P_MESS_URL_COPY_ERROR_HINT_TITLE'),
                'TYPE' => 'STRING',
                'DEFAULT' => Loc::getMessage('BSN_P_MESS_URL_COPY_ERROR_HINT_DEFAULT'),
            ],
        ]
    );

    if ($arCurrentValues['SHOW_SHARE_SOCIALS'] !== 'N') {
        $arComponentParameters['PARAMETERS'] = array_merge(
            $arComponentParameters['PARAMETERS'],
            [
                'MESS_SHARE_SOCIALS_TITLE' => [
                    'PARENT' => 'MESSAGES',
                    'NAME' => Loc::getMessage('BSN_P_MESS_SHARE_SOCIALS_TITLE_TITLE'),
                    'TYPE' => 'STRING',
                    'DEFAULT' => Loc::getMessage('BSN_P_MESS_SHARE_SOCIALS_TITLE_DEFAULT'),
                ],
            ]
        );
    }

    if ($arCurrentValues['SHOW_QRCODE'] !== 'N') {
        $arComponentParameters['PARAMETERS'] = array_merge(
            $arComponentParameters['PARAMETERS'],
            [
                'MESS_QR_CODE_HINT' => [
                    'PARENT' => 'MESSAGES',
                    'NAME' => Loc::getMessage('BSN_P_MESS_QR_CODE_HINT_TITLE'),
                    'TYPE' => 'STRING',
                    'DEFAULT' => Loc::getMessage('BSN_P_MESS_QR_CODE_HINT_DEFAULT'),
                ],
            ]
        );
    }
}
