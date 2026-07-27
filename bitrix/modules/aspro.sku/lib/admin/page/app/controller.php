<?php

namespace Aspro\Sku\Admin\Page\App;

use Aspro\Sku\Admin\Page\Helper;
use Aspro\Sku\General;
use Bitrix\Main\HttpRequest;

class Controller
{
    private string $controllerName = '';
    private string $controllerAction = '';

    private array $errors = [];
    private View $view;

    public function __construct(protected ?HttpRequest $request = null)
    {
        $this->mkControllerName();
        $this->processView();
    }

    private function mkControllerName()
    {
        $this->controllerName = strtolower(implode('.', array_filter(explode('\\', str_replace(str_replace('App', 'Controllers', __NAMESPACE__), '', static::class)))));
    }

    private function processView()
    {
        $this->view = new View();
        $this->view->setViewPath($this->getViewPath());
        $this->view->setFolderName($this->getViewFolderName());
        $this->view->setUnique(uniqid());
    }

    public function getViewPath()
    {
        return $this->getView()->getRootPathViews().DIRECTORY_SEPARATOR.$this->getViewFolderName().DIRECTORY_SEPARATOR;
    }

    public function getView()
    {
        return $this->view;
    }

    public function getViewFolderName()
    {
        return str_replace('.', DIRECTORY_SEPARATOR, $this->getControllerName());
    }

    protected function getControllerName()
    {
        return $this->controllerName;
    }

    public function render($view, array $params = [])
    {
        $this->appendParams($params);

        return $this->getView()->render($view, $params);
    }

    private function appendParams(array &$params)
    {
        $params['moduleClassList'] = str_replace('.', '-', General::moduleId);
        $params['controllerAction'] = $this->getControllerAction();
        $params['controllerName'] = $this->getControllerName();
        $params['controllerClassList'] = str_replace('.', '-', $this->getControllerName());
    }

    protected function getControllerAction()
    {
        return $this->controllerAction;
    }

    public function setControllerAction($action)
    {
        $this->controllerAction = $action;
    }

    protected function getErrors()
    {
        return $this->errors;
    }

    protected function hasErrors()
    {
        return $this->errors
          ? true
          : false;
    }

    protected function setErrors($errors)
    {
        if (is_array($errors)) {
            $this->errors = array_map(function ($item) {
                return $item;
            }, $errors);
        } else {
            $this->errors[] = $errors;
        }
    }

    protected function addError($error)
    {
        $this->errors[] = $error;
    }

    protected function validateAjaxAction(): void
    {
        if (!$this->request->isAjaxRequest()) {
            Helper::throwException(General::getMessage('ERROR_AJAX_REQUEST_EXPECTED'));
        }

        if (!$this->request->isPost()) {
            Helper::throwException(General::getMessage('ERROR_POST_REQUEST_EXPECTED'));
        }

        if (!check_bitrix_sessid()) {
            Helper::throwException(General::getMessage('ERROR_BITRIX_SESSION_ID_NOT_FOUND'));
        }

        if (!$this->request->getPost('ID')) {
            Helper::throwException(General::getMessage('ERROR_ID_REQUEST_PARAMETER_EXPECTED'));
        }
    }

    protected function checkRightsToWrite(): void
    {
        if (!$this->hasRightsToWrite()) {
            Helper::throwException(General::getMessage('ERROR_INSUFFICIENT_RIGHTS_TO_WRITE'));
        }
    }

    protected function hasRightsToWrite(): bool
    {
        return $GLOBALS['APPLICATION']->GetGroupRight(General::moduleId) >= 'W';
    }
}
