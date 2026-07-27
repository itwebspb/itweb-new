<?php

namespace Aspro\Sku\Admin\Page\Controllers\Rules\Sku;

use Aspro\Sku\Admin\Page\Controllers;
use Aspro\Sku\Admin\Page\Router;
use Aspro\Sku\Admin\Page\Services;
use Aspro\Sku\Admin\Page\Ui;
use Aspro\Sku\Enums\ProfileSkuTypes;
use Aspro\Sku\General;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Detail extends Controllers\Detail
{
    protected function getUi(): Ui\Rules\Sku\Detail
    {
        return Ui\Rules\Sku\Detail::getInstance();
    }

    protected function getService(): Services\Rules\Sku
    {
        return Services\Rules\Sku::getInstance();
    }

    public function actionView(?int $id = null)
    {
        $this->actionDetail($id);
    }

    protected function createEntity($fields = null): string
    {
        $this->checkRightsToWrite();

        $service = $this->getService();

        return $service->create($fields);
    }

    protected function getActionUrl(): string
    {
        if (!$this->data || $this->isCopyAction()) {
            return Router::mkUrl($this->getControllerName(), 'create');
        }

        return Router::mkUrl('rules.sku', 'update');
    }

    protected function getData(?int $id = null): array
    {
        $data = [];
        if ($id) {
            $data = $this->getElementsByFilter(['ID' => $id]);

            if ($this->isCopyAction()) {
                unset($data['ID']);
            }
        }

        return $data;
    }

    private function getElementsByFilter(array $filter = []): ?array
    {
        return $this->getService()->getRowByFilter($filter);
    }

    protected function getTabs(): array
    {
        return $this->getUi()->getTabs($this->getService()->getFields(), $this->data, $this->getControllerAction());
    }

    protected function mkPageTitle(): string
    {
        $pageTitle = [
            General::getMessage('MENU__RULES_TEXT'),
        ];

        $elementName = $this->data['NAME'];
        if (!$elementName || $this->isCopyAction()) {
            $pageTitle[] = General::getMessage('UI_ACTION_ADDING');
        } else {
            $pageTitle[] = $elementName.' - '.General::getMessage('UI_ACTION_EDITING');
        }

        return implode(': ', $pageTitle);
    }
}
