<?php

/**
 * BcCsvImportSampleOrderDetails 設定
 *
 * 1対多CSVインポートのサンプルプラグイン。
 * 受注ヘッダー（1）＋受注明細（多）をまとめてインポートする。
 */
return [
    'BcApp' => [
        'adminNavigation' => [
            'Contents' => [
                'BcCsvImportSampleOrderDetails' => [
                    'title' => __d('baser_core', '受注明細CSVインポート サンプル'),
                    'url' => [
                        'Admin' => true,
                        'plugin' => 'BcCsvImportSampleOrderDetails',
                        'controller' => 'sample_order_details_csv_imports',
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'BcCsvImportCore' => [
        'showImportStrategySelect' => true,
        'defaultImportStrategy'    => 'append',
        'showDuplicateModeSelect'  => true,
        'defaultDuplicateMode'     => 'skip',
    ],
];
