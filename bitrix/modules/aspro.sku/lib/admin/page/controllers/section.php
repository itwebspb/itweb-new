<?php

namespace Aspro\Sku\Admin\Page\Controllers;

use Aspro\Sku\Admin\Page\App\Controller;
use Aspro\Sku\Admin\Page\Helper;
use Aspro\Sku\Admin\Page\Services;
use Aspro\Sku\Admin\Page\Ui;
use Aspro\Sku\General;

abstract class Section extends Controller
{
    abstract protected function getUi(): Ui\Section;

    abstract protected function getService(): Services\Base;

    abstract protected function getDetailElementUrl(array $row): string;

    abstract protected function prepareElementRow(array $row, \CAdminUiListRow &$rowGrid): void;

    abstract protected function copyEntity(string $id): string;

    abstract protected function deleteEntity(string $id): string;

    abstract protected function updateEntity(string $id, ?array $fields = null): string;

    public function actionList()
    {
        $order = [
            'by' => 'ID',
            'order' => 'DESC',
        ];

        if ($this->request->get('by') && $this->request->get('order')) {
            $order['by'] = mb_strtoupper($this->request->get('by'));
            $order['order'] = mb_strtoupper($this->request->get('order'));
        }

        $ui = $this->getUi();

        $oSort = new \CAdminUiSorting($ui->getGridId(), $order['by'], $order['order']);
        $order['by'] = mb_strtoupper($oSort->getField());
        $order['order'] = mb_strtoupper($oSort->getOrder());

        $adminUiList = new \CAdminUiList($ui->getGridId(), $oSort);

        if ($this->hasRightsToWrite()) {
            $adminUiList->AddAdminContextMenu($ui->getContextMenu(), $isShowExcel = false);
        }

        $adminUiList->AddHeaders($ui->getGridColumns());

        $filter = [];
        $adminUiList->AddFilter($ui->getFilterFields(), $filter);

        $this->processGroupActions($adminUiList);

        $rsData = new \CAdminUiResult(
            $this->getService()->getRsElements([$order['by'] => $order['order']], $filter),
            $ui->getGridId()
        );
        $rsData->NavStart();
        $adminUiList->SetNavigationParams($rsData);

        $this->prepareElementsList($rsData, $adminUiList);

        $this->render($this->getControllerAction(), [
            'adminUiList' => $adminUiList,
            'filterFields' => $ui->getFilterFields(),
        ]);
    }

    private function prepareElementsList(\CDBResult $rsData, \CAdminUiList &$adminUiList)
    {
        while ($row = $rsData->Fetch()) {
            $rowGrid = $adminUiList->AddRow($row['ID'], $row, $this->getDetailElementUrl($row), General::getMessage('UI_ACTION_EDIT_ELEMENT'));

            $this->prepareElementRow($row, $rowGrid);
            $this->addActionsInRow($row, $rowGrid, $adminUiList);
        }
    }

    private function addActionsInRow(array $row, \CAdminUiListRow &$rowGrid, \CAdminUiList $adminUiList): void
    {
        $rowGrid->AddActions($this->getActionsInRow($row, $adminUiList));
    }

    private function getActionsInRow(array $row, \CAdminUiList $adminUiList): array
    {
        $actions = [
            [
                'TEXT' => General::getMessage('UI_ACTION_EDIT'),
                'ACTION' => $adminUiList->ActionRedirect($this->getDetailElementUrl($row)),
                'DEFAULT' => true,
            ],
        ];

        if ($this->hasRightsToWrite()) {
            $actions = array_merge($actions, $this->getMoreActionsInRow($row, $adminUiList));
        }

        return $actions;
    }

    protected function getMoreActionsInRow(array $row, \CAdminUiList $adminUiList): array
    {
        return [];
    }

    protected function processGroupActions(\CAdminUiList &$adminUiList): void
    {
        $this->listenEditAction($adminUiList);
        $this->listenGroupActions($adminUiList);

        if ($this->hasRightsToWrite()) {
            $this->addGroupActions($adminUiList);
        }
    }

    private function listenEditAction(\CAdminUiList &$adminUiList)
    {
        if ($adminUiList->EditAction()) {
            $allFields = $this->request->getPost('FIELDS');

            try {
                foreach ($allFields as $rowId => $fields) {
                    if (!$adminUiList->IsUpdated($rowId)) {
                        continue;
                    }

                    $this->updateEntity($rowId, $fields);
                }
            } catch (\Exception $e) {
                $adminUiList->AddGroupError($e->getMessage());
            }
        }
    }

    private function listenGroupActions(\CAdminUiList &$adminUiList)
    {
        if (!$action = $this->request->get('action_button_'.$adminUiList->table_id)) {
            return;
        }

        if (!$ids = $adminUiList->GroupAction()) {
            return;
        }

        try {
            switch ($action) {
                case 'delete':
                    foreach ($ids as $id) {
                        $this->deleteEntity($id);
                    }
                    break;
                case 'activate':
                case 'deactivate':
                    foreach ($ids as $id) {
                        $this->updateEntity($id, ['ACTIVE' => $action === 'activate' ? 'Y' : 'N']);
                    }
                    break;
                case 'copy':
                    foreach ($ids as $id) {
                        $this->copyEntity($id);
                    }
                    break;
            }
        } catch (\Exception $e) {
            $adminUiList->AddGroupError($e->getMessage());
        }
    }

    protected function addGroupActions(\CAdminUiList &$adminUiList): void
    {
        $adminUiList->AddGroupActionTable([
            'delete' => '',
            'edit' => '',
        ] + $this->getMoreGroupActions());
    }

    protected function getMoreGroupActions(): array
    {
        return [];
    }

    public function actionCopy()
    {
        $this->validateAjaxAction();

        $id = $this->copyEntity($this->request->getPost('ID'));

        Helper::successJsonResponse('Entity copied successfully', ['ID' => $id]);
    }

    public function actionDelete()
    {
        $this->validateAjaxAction();

        $id = $this->deleteEntity($this->request->getPost('ID'));

        Helper::successJsonResponse('Entity deleted successfully', ['ID' => $id]);
    }

    public function actionUpdate()
    {
        $this->validateAjaxAction();

        $id = $this->updateEntity($this->request->getPost('ID'), $this->request->getPost('FIELDS'));

        Helper::successJsonResponse('Entity updated successfully', ['ID' => $id]);
    }
}
