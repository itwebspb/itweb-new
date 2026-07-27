<?php

namespace Aspro\Sku\Admin\Page;

use Aspro\Sku\General;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Router
{
    public const FOLDER_URL = '/bitrix/admin/';
    public const ROUTE_FILE = 'router.php';

    public const ACTION_DELIMETER = '@';

    public function __construct(private ?HttpRequest $request = null)
    {
        if (!$request) {
            $this->request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        }
    }

    public function resolve()
    {
        $routeParam = $this->request->getQuery('route');

        list($controllerName, $methodName) = explode(self::ACTION_DELIMETER, $routeParam);

        $controllerClass = __NAMESPACE__.'\\Controllers\\'.$this->normalizeName(str_replace('.', '\\', $controllerName));
        $methodClass = 'action'.$this->normalizeName($methodName);

        if (!$routeParam) {
            Helper::throwException(General::getMessage('ERROR_BAD_PARAMS'));
        }

        if (!class_exists($controllerClass) || !method_exists($controllerClass, $methodClass)) {
            Helper::throwException(General::getMessage('ERROR_CLASS_OR_METHOD_NOT_EXISTED'));
        }

        $controller = new $controllerClass($this->request);
        $controller->setControllerAction($methodName);
        call_user_func_array([$controller, $methodClass], $this->getMethodArgs($controllerClass, $methodClass));
    }

    private function normalizeName($name)
    {
        return ucfirst(str_replace(['-', ':', '/', '_'], '', $name));
    }

    private function getMethodArgs($class, $method)
    {
        $result = [];

        $reflection = new \ReflectionClass($class);
        $methodReflection = $reflection->getMethod($method);
        foreach ($methodReflection->getParameters() as $i => $param) {
            if ($_value = $this->request->getQuery($param->getName())) {
                $result[mb_strtolower($param->getName())] = $_value;
            } else {
                $result[mb_strtolower($param->getName())] = $param->getDefaultValue();
            }
        }

        return $result;
    }

    public static function isNeedIncludeAdminVisual(HttpRequest $request)
    {
        return !$request->isAjaxRequest() || ($request->isAjaxRequest() && !$request->isPost());
    }

    public static function mkUrl($controller, $action, $params = [])
    {
        $params['lang'] = urlencode(LANGUAGE_ID);

        $route = $controller.self::ACTION_DELIMETER.$action;

        return self::FOLDER_URL.General::assetsPath.'/'.self::ROUTE_FILE.'?'.
            implode('&', array_filter(
                [
                    'route='.$route,
                    http_build_query($params),
                ]
            ));
    }
}
