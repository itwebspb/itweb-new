<?php

namespace Aspro\Sku\Admin\Page\Services\Rules;

use Aspro\Sku\Admin\Page\Helper;
use Aspro\Sku\Admin\Page\Services\Base;
use Aspro\Sku\ORM\RulesSkuTable;
use Bitrix\Main\Localization\Loc;

class Sku extends Base
{
    private string $rowId = '';

    protected function checkRow($id): void
    {
        if (!RulesSkuTable::getById($id)) {
            Helper::throwException(Loc::getMessage('INCORRECT_ID'));
        }
    }

    public function copy($id)
    {
        $this->action('processCopy', $id);

        return $this->getRowId();
    }

    private function getRowId()
    {
        return $this->rowId;
    }

    protected function processCopy($id)
    {
        $source = $this->getRowByFilter(['ID' => $id]);

        $source['NAME'] = '[copy id: '.$source['ID'].'] '.$source['NAME'];
        unset($source['ID']);

        $result = RulesSkuTable::add($source);

        if (!$result->isSuccess()) {
            Helper::throwException($result->getErrorMessages());
        }

        $this->setRowId($result->getId());
    }

    private function setRowId($id)
    {
        $this->rowId = $id;
    }

    public function getRowByFilter(array $filter = []): ?array
    {
        $result = RulesSkuTable::getRow([
            'select' => [
                '*',
            ],
            'filter' => $filter,
            'cache' => [
                'ttl' => RulesSkuTable::getCacheTtl(),
            ],
        ]);

        if (!$result) {
            Helper::throwException(Loc::getMessage('ROW_NOT_FOUND'));
        }

        return $result;
    }

    public function delete($id)
    {
        $this->action('processDelete', $id);

        return $this->getRowId();
    }

    protected function processDelete($id)
    {
        $result = RulesSkuTable::delete($id);

        if (!$result->isSuccess()) {
            Helper::throwException($result->getErrorMessages());
        }

        $this->setRowId($id);
    }

    public function create($fields)
    {
        $this->action('processCreate', $fields);

        return $this->getRowId();
    }

    protected function processCreate($fields)
    {
        if (!$createFields = $this->checkFields($fields)) {
            return;
        }

        $result = RulesSkuTable::add($createFields);

        if (!$result->isSuccess()) {
            Helper::throwException($result->getErrorMessages());
        }

        $this->setRowId($result->getId());
    }

    private function checkFields($fields): ?array
    {
        if (!$fields) {
            return null;
        }

        $filteredFields = array_filter($fields, fn ($field) => RulesSkuTable::hasMapField($field), ARRAY_FILTER_USE_KEY);

        if (!$filteredFields) {
            return null;
        }

        return $filteredFields;
    }

    public function update($id, $fields)
    {
        $this->action('processUpdate', $id, $fields);

        return $this->getRowId();
    }

    protected function processUpdate($id, $fields)
    {
        if (!$updateFields = $this->checkFields($fields)) {
            return;
        }

        $result = RulesSkuTable::update($id, $updateFields);

        if (!$result->isSuccess()) {
            Helper::throwException($result->getErrorMessages());
        }

        $this->setRowId($result->getId());
    }

    public function getElements($order = [], $filter = []): array
    {
        if ($filter['NAME']) {
            $filter['NAME'] = '%'.$filter['NAME'].'%';
        }

        $rsRows = RulesSkuTable::getList([
            'select' => [
                '*',
            ],
            'order' => $order,
            'filter' => $filter,
            'cache' => [
                'ttl' => RulesSkuTable::getCacheTtl(),
            ],
        ]);

        $elements = [];
        while ($row = $rsRows->fetch()) {
            $elements[] = $row;
        }

        return $elements;
    }

    public function getFields(): array
    {
        return RulesSkuTable::getEntity()->getFields();
    }
}
