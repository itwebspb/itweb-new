<?php

namespace Aspro\Popup;

class Events
{
    public static function OnAsproAfterShowFooterHandler()
    {
        global $APPLICATION;

        $APPLICATION->IncludeComponent(
            'aspro:marketing.popup',
            '.default',
            [],
            false,
            ['HIDE_ICONS' => 'Y']
        );
    }
}
