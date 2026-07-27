<?php

namespace Aspro\Max\Template\Common;

use Aspro\Max\Utils as SolutionUtils;
use Aspro\Max\Functions\Extensions;
use CMax as Solution;

class IncludeAreas
{
    public static function showFooterUserBlock(string $optionalClass = ''): void
    {
        ob_start();
            $GLOBALS['APPLICATION']->IncludeFile(SITE_DIR.'include/footer/user_include_area.php', [], [
                'MODE' => 'php',
                'NAME' => GetMessage('FOOTER_USER_INCLUDE_AREA'),
                'TEMPLATE' => 'include_area.php',
            ]);
        $html = trim(ob_get_clean());

        if (strlen($html)) {
            Extensions::init('gutters');

            $classList = ['black'];
            if ($optionalClass) {
                $classList[] = $optionalClass;
            }

            $classList = array_filter($classList);
            $containerClass = htmlspecialcharsbx(SolutionUtils::implodeClasses($classList));

            echo "<div class='{$containerClass}'>{$html}</div>";
        }
    }

    public static function showFooterPolicy(): void
    {
        $policies = [
            ['name' => 'confidentiality', 'iconKey' => 'privacy_policy'],
            ['name' => 'cookies_policy', 'iconKey' => 'cookies_policy'],
            ['name' => 'public_offer', 'iconKey' => 'public_offer'],
        ];

        foreach ($policies as $policy) {
            self::renderPolicyBlock($policy['name'], $policy['iconKey']);
        }
    }

    private static function renderPolicyBlock(string $name, string $iconKey): void
    {
        $filePath = SITE_DIR.'include/footer/'.$name.'.php';
        ob_start();
        $GLOBALS['APPLICATION']->IncludeFile($filePath, [], [
            'MODE'   => 'php',
            'NAME'   => $name,
            'TEMPLATE' => 'include_area.php'
        ]);
        $html = trim(ob_get_clean());

        if (strlen($html)) {
            $fullPath = SITE_TEMPLATE_PATH.'/images/svg/policy-icons.svg#'.$iconKey;
            ?>
            <div class="confidentiality">
                <?= Solution::showSpriteIconSvg($fullPath, $iconKey, ['WIDTH' => 18, 'HEIGHT' => 16]); ?>
                <div class="flex1"><?= $html ?></div>
            </div>
            <?php
        }
    }
}
