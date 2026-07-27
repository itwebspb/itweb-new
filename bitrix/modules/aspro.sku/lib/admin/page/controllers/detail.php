<?php

namespace Aspro\Sku\Admin\Page\Controllers;

use Aspro\Sku\Admin\Page\App\Controller;
use Aspro\Sku\Admin\Page\Helper;
use Aspro\Sku\Admin\Page\Services;
use Aspro\Sku\Admin\Page\Ui;

abstract class Detail extends Controller
{
    protected array $data = [];

    abstract protected function getActionUrl(): string;

    abstract protected function getUi(): Ui\Detail;

    abstract protected function getService(): Services\Base;

    abstract protected function getData(?int $id = null): array;

    abstract protected function getTabs(): array;

    abstract protected function mkPageTitle(): string;

    abstract protected function createEntity($fields = null): string;

    protected function actionDetail(?int $id = null)
    {
        $this->data = $this->getData($id);
        $tabs = $this->getTabs();

        $this->render('detail', [
            'pageTitle' => $this->mkPageTitle(),
            'actionUrl' => $this->getActionUrl(),
            'adminContextMenu' => $this->getToolbar(['action' => $this->getControllerAction(), 'data' => $this->data]),
            'adminTabControl' => new \CAdminTabControl(str_replace('.', '_', $this->getControllerName()).'_tabs', $tabs, bCanExpand: false, bDenyAutoSave: true),
            'data' => $this->data,
            'tabs' => $tabs,
            'tabButtons' => $this->getTabButtons(),
        ]);
    }

    protected function getToolbar(array $params = [])
    {
        $params['hasRightsToWrite'] = $this->hasRightsToWrite();
        $params['isCopyAction'] = $this->isCopyAction();

        return new \CAdminContextMenu($this->getUi()->getContextMenu($params));
    }

    protected function isCopyAction()
    {
        return $this->request->get('copy') === 'Y';
    }

    protected function getTabButtons(): string
    {
        return $this->getUi()->getTabButtons($this->hasRightsToWrite());
    }

    public function actionCreate()
    {
        $this->validateAjaxAction();

        $id = $this->createEntity($this->request->getPost('FIELDS'));

        Helper::successJsonResponse('Entity created successfully', ['ID' => $id]);
    }
}
