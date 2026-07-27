<?php

namespace Aspro\Sku\Admin\Page\App;

use Aspro\Sku\General;

class View
{
    private $viewPath;
    private $folderName;
    private $unique;
    private string $rootViewsPath = '';

    public function __construct(?Request $request = null)
    {
        $this->rootViewsPath = General::getModuleRoot().DIRECTORY_SEPARATOR.join(DIRECTORY_SEPARATOR, ['admin', 'views']);
    }

    public function render($view, array $params = [])
    {
        $file = $this->getViewPath().$view.'.php';

        $this->includeFile($file, $params);
    }

    public function getViewPath()
    {
        return $this->viewPath;
    }

    public function setViewPath($path)
    {
        $this->viewPath = $path;
    }

    protected function includeFile($file, array $params)
    {
        if (file_exists($file)) {
            extract($params);
            include $file;
        }
    }

    public function setFolderName($value)
    {
        $this->folderName = $value;
    }

    public function getFolderName()
    {
        return $this->folderName;
    }

    public function setUnique($value)
    {
        $this->unique = $value;
    }

    public function getUnique()
    {
        return $this->unique;
    }

    public function getPathToScriptByRoute()
    {
        return $this->getPathToModuleScripts().$this->getFolderName();
    }

    public function getPathToModuleScripts()
    {
        return '/bitrix/js/'.General::assetsPath.'/';
    }

    public function getPathToStyleByRoute()
    {
        return $this->getPathToModuleStyles().$this->getFolderName();
    }

    public function getPathToModuleStyles()
    {
        return '/bitrix/css/'.General::assetsPath.'/';
    }

    public function getRootPathViews()
    {
        return $this->rootViewsPath;
    }
}
