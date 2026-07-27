<?php

namespace Aspro\Sku\Utils;

use Aspro\Sku\General;
use Bitrix\Main\IO\File;

class View
{
    public function __construct(
        private array $params = [],
        private string $componentName = 'sku.list',
        private string $templateFolder = ''
    ) {}

    public function showPropsHtml(array $arProps = [], array $arOptions = [])
    {
        $arDefaultOptions = [
            'HIDE_WITH_ONE_VALUE' => false,
            'LAZY' => true,
            'SHOW_HINT' => $this->params['SHOW_HINT'],
        ];
        $arConfig = array_merge($arDefaultOptions, $arOptions);

        $bShowProps = false;

        if ($arConfig['HIDE_WITH_ONE_VALUE']) {
            foreach ($arProps as $code => $arProp) {
                if (count($arProp['VALUES']) > 1) {
                    $bShowProps = true;
                }
            }
        } else {
            $bShowProps = true;
        }

        if ($bShowProps) {
            foreach ($arProps as $code => $arProp) {
                $this->showPropsView([
                    'FILE' => 'properties_in_'.$arProp['SHOW_MODE'].'.php',
                    'LAZY' => $arConfig['LAZY'],
                    'PARAMS' => $arProp,
                    'TYPE' => 'SKU',
                    'SHOW_HINT' => $arConfig['SHOW_HINT'] === 'Y',
                    'BASE_PATH' => $arConfig['BASE_PATH'],
                ]);
            }
        }
    }

    private function showPropsView($arOptions = [])
    {
        global $APPLICATION;
        $arDefaultOptions = [
            'TYPE' => '',
            'BASE_PATH' => '',
            'FILE' => '',
            'PARAMS' => [],
        ];
        $arConfig = array_merge($arDefaultOptions, $arOptions);

        if (!$arConfig['FILE']) {
            return;
        }

        $filePath = $this->resolveViewFilePath($arConfig);
        if ($filePath) {
            include $filePath;
        }
    }

    /**
     * Resolve file path for view.
     *
     * file path search algo:
     *  - find file in custom path
     *  - else find file in public folder
     *  - else find file in template directory
     */
    private function resolveViewFilePath(array $config): string
    {
        $basePathList = [];

        if ($config['BASE_PATH']) {
            $basePathList['custom'] = $config['BASE_PATH'];
        }

        $basePathList['public'] = SITE_DIR.'/include/blocks/'.$this->componentName.'/views/';
        if ($this->templateFolder) {
            $basePathList['template'] = $this->templateFolder.'/views/';
        }

        foreach ($basePathList as $type => $path) {
            $filePath = str_replace('//', '/', General::getDocRoot().$path.$config['FILE']);

            if (File::isFileExists($filePath)) {
                return $filePath;
            }
        }

        return '';
    }
}
