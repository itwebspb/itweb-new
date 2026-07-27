<?php
/**
 *  @var array $dataFilterRule
 *  @var array $gridSearch
 */

use Aspro\Smartseo,
    Aspro\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;
?>
<?
$gridRows = [];
foreach ($gridSearch['ROWS'] as $row) {
    $gridRow = $row;

    $gridRow['ACTIVE'] = $row['ACTIVE'] === 'Y' ? Loc::getMessage('SMARTSEO_GRID_SEARCH_VALUE_Y') : Loc::getMessage('SMARTSEO_GRID_SEARCH_VALUE_N');

    $gridRow['DISPLAY_FILTER_CONDITION'] = '';
    if($row['FILTER_CONDITION_NAME']) {
        $gridRow['FILTER_CONDITION'] = '[' . $row['FILTER_CONDITION_ID'] . '] ' . $row['FILTER_CONDITION_NAME'];
    } else {
        $gridRow['FILTER_CONDITION'] = Loc::getMessage('SMARTSEO_GRID_SEARCH_VALUE_CONDITION_DEFAULT', [
            '#ID#' => $row['FILTER_CONDITION_ID']
        ]);
    }

    $data = [];
    foreach ($gridSearch['COLUMNS'] as $column) {
        $data[$column['id']] = $gridRow[$column['field']];
    }

    $gridRows[] = [
        'data' => $data,
        'actions' => [
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_SEARCH_ACTION_EDIT'),
                'default' => true,
                'onclick' => vsprintf("contextMenuGridSearch.actionEdit(%d, '%s')", [
                    'ID' => $row['ID'],
                    'NAME' =>
                      htmlspecialchars($row['FILTER_CONDITION_NAME'], ENT_QUOTES)
                      ?: Loc::getMessage('SMARTSEO_GRID_SEARCH_VALUE_CONDITION_DEFAULT', [
                            '#ID#' => $row['FILTER_CONDITION_ID'],
                      ])
                ])
            ],
            [
                'text' => $row['ACTIVE'] == 'Y'
                    ? Loc::getMessage('SMARTSEO_GRID_SEARCH_ACTION_DEACTIVATE')
                    : Loc::getMessage('SMARTSEO_GRID_SEARCH_ACTION_ACTIVATE'),
                'default' => true,
                'onclick' => $row['ACTIVE'] == 'Y'
                    ? "contextMenuGridSearch.actionDeactivate($row[ID])"
                    : "contextMenuGridSearch.actionActivate($row[ID])"
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_SEARCH_ACTION_REINDEX'),
                'default' => true,
                'onclick' => "contextMenuGridSearch.actionReindex($row[ID])"
            ],
            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_SEARCH_ACTION_DELETE'),
                'default' => true,
                'onclick' => "contextMenuGridSearch.actionDelete($row[ID])"
            ],
        ]
    ];
}
?>
<?
$gridSnippet = new Bitrix\Main\Grid\Panel\Snippet();

$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $gridSearch['GRID_ID'],
    'COLUMNS' => $gridSearch['COLUMNS'],
    'ROWS' => $gridRows,
    'FOOTER' => [
        'TOTAL_ROWS_COUNT' => $gridSearch['TOTAL_ROWS_COUNT'],
    ],
    'SHOW_ROW_CHECKBOXES' => true,
    'NAV_OBJECT' => $gridSearch['NAV_OBJECT'],
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => '10', 'VALUE' => '10'],
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => true,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => true,
    'SHOW_TOTAL_COUNTER' => true,
    'SHOW_PAGESIZE' => true,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => false,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => false,
    'AJAX_OPTION_HISTORY' => 'N',
    'ACTION_PANEL' => [
        'GROUPS' => [
            'TYPE' => [
                'ITEMS' => [
                    [
                        'TYPE' => \Bitrix\Main\Grid\Panel\Types::BUTTON,
                        'ID' => 'delete',
                        'CLASS' => Bitrix\Main\Grid\Panel\DefaultValue::REMOVE_BUTTON_CLASS,
                        'TEXT' => Loc::getMessage('SMARTSEO_GRID_SEARCH_ACTION_DELETE'),
                        'ONCHANGE' => [
                            [
                                'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
                                'DATA' => [
                                    [
                                        'JS' => 'contextMenuGridSearch.actionDeleteSelected()'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        "TYPE" => "DROPDOWN",
                        "ID" => "base_action_select_".$gridSearch['GRID_ID'],
                        "NAME" => "action_button_".$gridSearch['GRID_ID'],
                        "ITEMS" => [
                            [
                                "NAME" => GetMessage("admin_lib_list_actions"),
                                "VALUE" => "default",
                                "ONCHANGE" => [ ["ACTION" => "RESET_CONTROLS",]]
                            ],
                            [
                                "NAME" => Loc::getMessage('SMARTSEO_GRID_SEARCH_PANEL_ACTION_ACTIVATE'),
                                "VALUE" => "activate",
                                "ONCHANGE" => [
                                    ["ACTION" => "RESET_CONTROLS" ],
                                    [
                                        "ACTION" => "CREATE",
                                        "DATA" => [$gridSnippet->getApplyButton([
                                            "ONCHANGE" => [[
                                                    "ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
                                                    "DATA" => [
                                                        [
                                                            'JS' => 'contextMenuGridSearch.actionActivateSelected()'
                                                        ]
                                                    ]
                                                ]]
                                            ]
                                        )]
                                    ]
                                ]
                            ],
                            [
                                "NAME" => Loc::getMessage('SMARTSEO_GRID_SEARCH_PANEL_ACTION_DEACTIVATE'),
                                "VALUE" => "deactivate",
                                "ONCHANGE" => [
                                    ["ACTION" => "RESET_CONTROLS"],
                                    [
                                        "ACTION" => "CREATE",
                                        "DATA" => [$gridSnippet->getApplyButton([
                                            "ONCHANGE" => [[
                                                    "ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
                                                    "DATA" => [
                                                        [
                                                            'JS' => 'contextMenuGridSearch.actionDeactivateSelected()'
                                                        ]
                                                    ]
                                                ]]
                                            ]
                                        )]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],
        ],
    ],
]);
?>
<script>
    var phpObjectGridSearch = <?= CUtil::PhpToJSObject([
        'gridId' => $gridSearch['GRID_ID'],
        'filterRuleId' => $dataFilterRule['ID'],
        'urls' => [
            'ACTION_ACTIVATE' => Helper::url('filter_search/activate', ['sessid' => bitrix_sessid()]),
            'ACTION_DEACTIVATE' => Helper::url('filter_search/deactivate', ['sessid' => bitrix_sessid()]),
            'ACTION_DELETE' => Helper::url('filter_search/delete', ['sessid' => bitrix_sessid()]),
            'ACTION_REINDEX' => Helper::url('filter_search/reindex', ['sessid' => bitrix_sessid()]),
            'DETAIL_PAGE' => Helper::url('filter_search/detail', ['sessid' => bitrix_sessid()]),
        ]
    ]) ?>;

    BX.message({
        SMARTSEO_POPUP_SEARCH_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_SEARCH_BTN_DELETE') ?>',
        SMARTSEO_POPUP_SEARCH_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_SEARCH_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_SEARCH_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_POPUP_SEARCH_BTN_CLOSE') ?>',
        SMARTSEO_POPUP_SEARCH_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_SEARCH_MESSAGE_DELETE') ?>',
        SMARTSEO_GRID_SEARCH_NEW: '<?= Loc::getMessage('SMARTSEO_GRID_SEARCH_NEW') ?>',
        SMARTSEO_GRID_SEARCH_MESSAGE_REINDEX_SUCCESS: '<?= Loc::getMessage('SMARTSEO_GRID_SEARCH_MESSAGE_REINDEX_SUCCESS') ?>',
    });

    BX.ready(function ()
    {
      var gridObject = BX.Main.gridManager.getById('<?= $gridSearch['GRID_ID'] ?>');

      if (gridObject.hasOwnProperty('instance')) {
        gridObject.instance.reloadTable(null, null);
      }
    })
</script>
