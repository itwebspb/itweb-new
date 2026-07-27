<?php

namespace Aspro\Sku\Admin\Page\Ui;

use Aspro\Sku\General;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class Detail extends Layout
{
    abstract public function getContextMenu(array $params = []): array;

    abstract public function getTabs(array $fields = [], array $data = [], string $action = ''): array;

    abstract protected function getTabButtonBack(): string;

    public function getTabButtons(bool $hasRightsToWrite = true): string
    {
        ob_start(); ?>

        <button type="button" name="save" class="ui-btn ui-btn-success" onclick="BX.Aspro?.Sku?.DetailManager?.send('save')" <?= !$hasRightsToWrite ? 'disabled' : ''; ?> title="<?= General::getMessage('UI_ACTION_SAVE_TITLE'); ?>">
            <?= General::getMessage('UI_ACTION_SAVE'); ?>
        </button>

        <button type="button" name="apply" class="ui-btn ui-btn-primary" onclick="BX.Aspro?.Sku?.DetailManager?.send('apply')" <?= !$hasRightsToWrite ? 'disabled' : ''; ?> title="<?= General::getMessage('UI_ACTION_APPLY_TITLE'); ?>">
            <?= General::getMessage('UI_ACTION_APPLY'); ?>
        </button>

        <button type="button" name="save_and_add" class="ui-btn ui-btn-success-light adm-btn-add" onclick="BX.Aspro?.Sku?.DetailManager?.send('saveadd')" <?= !$hasRightsToWrite ? 'disabled' : ''; ?> title="<?= General::getMessage('UI_ACTION_SAVE_AND_ADD_TITLE'); ?>">
            <?= General::getMessage('UI_ACTION_SAVE_AND_ADD'); ?>
        </button>

        <?$buttons = ob_get_clean();

        $buttons .= $this->getTabButtonBack();

        return $buttons;
    }
}
