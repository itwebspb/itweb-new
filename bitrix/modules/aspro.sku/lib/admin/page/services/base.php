<?php

namespace Aspro\Sku\Admin\Page\Services;

use Aspro\Sku\Admin\Page\Helper;
use Aspro\Sku\Admin\Traits\Singletonable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class Base
{
    use Singletonable;

    abstract protected function checkRow($id): void;

    abstract public function getElements($order = [], $filter = []): array;

    protected function action($actionMethod, ...$params): void
    {
        $id = current($params);
        if (!is_array($id) && $id) {
            $this->checkRow($id);
        }

        if (!method_exists($this, $actionMethod)) {
            Helper::throwException(Loc::getMessage('ACTION_METHOD_NOT_EXISTED'));
        }

        global $DB;

        try {
            $DB->StartTransaction();

            call_user_func_array([$this, $actionMethod], $params);

            $DB->Commit();
        } catch (\Exception $e) {
            $DB->Rollback();

            Helper::throwException($e->getMessage());
        }
    }

    public function getRsElements(array $order = [], array $filter = []): \CDBResult
    {
        $elements = $this->getElements($order, $filter);

        $result = new \CDBResult();
        $result->InitFromArray($elements);

        return $result;
    }
}
