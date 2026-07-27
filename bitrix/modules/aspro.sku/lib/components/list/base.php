<?php

namespace Aspro\Sku\Components\List;

use Aspro\Sku\General;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;

class Base extends \CBitrixComponent
{
    public function showPropsHtml(array $arProps = [], array $arOptions = [])
    {
        $arDefaultOptions = [
            'HIDE_WITH_ONE_VALUE' => false,
            'LAZY' => true,
            'SHOW_HINT' => $this->arParams['SHOW_HINT'] ?? 'N',
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

        $isUseWrapper = (empty($this->arParams['USE_WRAPPER']) || $this->arParams['USE_WRAPPER'] !== 'N');
        if ($isUseWrapper) {
            echo '<div class="sku-props">';
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

        if ($isUseWrapper) {
            echo '</div>';
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
     *  - else find file in component directory
     *  - else find in module
     */
    private function resolveViewFilePath(array $config): string
    {
        $basePathList = [];

        if ($config['BASE_PATH']) {
            $basePathList['custom'] = $config['BASE_PATH'];
        }

        $basePathList['public'] = SITE_DIR.'include/blocks/sku.list/views/';
        $basePathList['component'] = $this->getTemplate()->getFolder().'/views/';
        $basePathList['module'] = $this->getModuleViewsDir();

        foreach ($basePathList as $type => $path) {
            $filePath = str_replace('//', '/', General::getDocRoot().$path.$config['FILE']);

            if (File::isFileExists($filePath)) {
                return $filePath;
            }
        }

        return '';
    }

    private function getModuleViewsDir(): string
    {
        $viewGroup = 'default';
        $path = General::getRelativePath(General::getModuleRoot()).'/views/';

        $viewGroupInModule = $this->arParams['VIEW_GROUP_IN_MODULE'] ?? '';

        if ($viewGroupInModule && Directory::isDirectoryExists(General::getDocRoot().$path.$viewGroupInModule)) {
            $viewGroup = $viewGroupInModule;
        }

        return $path.$viewGroup.'/';
    }
}
