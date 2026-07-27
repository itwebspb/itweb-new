<?php

namespace Aspro\Sku\Admin\Page\Controllers\Rules;

use Aspro\Sku\Admin\Page\Controllers\Section;
use Aspro\Sku\Admin\Page\Router;
use Aspro\Sku\Admin\Page\Services;
use Aspro\Sku\Admin\Page\Ui;
use Aspro\Sku\Enums\RulesSkuFilterType;
use Aspro\Sku\General;

class Sku extends Section
{
    protected function getUi(): Ui\Rules\Sku
    {
        return Ui\Rules\Sku::getInstance();
    }

    protected function getService(): Services\Rules\Sku
    {
        return Services\Rules\Sku::getInstance();
    }

    protected function getDetailElementUrl(array $row): string
    {
        return Router::mkUrl($this->getControllerName().'.detail', 'view', ['id' => $row['ID']]);
        // return BX_ROOT.'/admin/'.General::assetsPath.'/rules/sku/detail.php?id='.$row['ID'];
    }

    protected function prepareElementRow(array $row, \CAdminUiListRow &$rowGrid): void
    {
        $rowGrid->AddCheckField('ACTIVE');

        $rowGrid->AddViewField(
            'NAME',
            '<a href="'.$this->getDetailElementUrl($row).'" title="'.General::getMessage('UI_ACTION_EDIT_ELEMENT').'" onclick="">'
                .htmlspecialcharsbx($row['NAME']).
            '</a>'
        );
        $rowGrid->AddInputField('NAME', ['size' => 50]);

        $rowGrid->AddInputField('SORT');

        $rowGrid->AddViewField('FILTER_TYPE', RulesSkuFilterType::getNamesWithLang()[$row['FILTER_TYPE']]);
    }

    protected function getMoreActionsInRow(array $row, \CAdminUiList $adminUiList): array
    {
        return $this->getUi()->getMoreActionsInRow($this->getControllerName(), $row);
    }

    protected function getMoreGroupActions(): array
    {
        return $this->getUi()->getMoreGroupActions();
    }

    protected function copyEntity($id): string
    {
        $this->checkRightsToWrite();

        $service = $this->getService();
        return $service->copy($id);
    }

    protected function deleteEntity($id): string
    {
        $this->checkRightsToWrite();

        $service = $this->getService();
        return $service->delete($id);
    }

    protected function updateEntity($id, $fields = null): string
    {
        $this->checkRightsToWrite();

        $service = $this->getService();
        return $service->update($id, $fields);
    }
}
