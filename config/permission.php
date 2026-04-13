<?php

/**
 * アクセスルール初期値
 */
return [
    'permission' => [

        'SampleOrderDetailsCsvImportsAdmin' => [
            'title' => __d('baser_core', '受注明細CSVインポート管理'),
            'plugin' => 'BcCsvImportSampleOrderDetails',
            'type' => 'Admin',
            'items' => [
                'Index' => [
                    'title' => __d('baser_core', 'CSVアップロード画面'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/index',
                    'method' => 'GET',
                    'auth' => true,
                ],
                'Upload' => [
                    'title' => __d('baser_core', 'CSVアップロード'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/upload',
                    'method' => 'POST',
                    'auth' => true,
                ],
                'ValidateBatch' => [
                    'title' => __d('baser_core', 'バリデーションバッチ'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/validate_batch',
                    'method' => 'POST',
                    'auth' => true,
                ],
                'ProcessBatch' => [
                    'title' => __d('baser_core', '登録バッチ'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/process_batch',
                    'method' => 'POST',
                    'auth' => true,
                ],
                'Status' => [
                    'title' => __d('baser_core', '進捗確認'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/status/*',
                    'method' => 'GET',
                    'auth' => true,
                ],
                'Cancel' => [
                    'title' => __d('baser_core', 'キャンセル'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/cancel/*',
                    'method' => 'POST',
                    'auth' => true,
                ],
                'Delete' => [
                    'title' => __d('baser_core', 'ジョブ削除'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/delete/*',
                    'method' => 'POST',
                    'auth' => true,
                ],
                'DownloadTemplate' => [
                    'title' => __d('baser_core', 'テンプレートCSVダウンロード'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/download_template',
                    'method' => 'GET',
                    'auth' => true,
                ],
                'DownloadErrors' => [
                    'title' => __d('baser_core', 'エラーCSVダウンロード'),
                    'url' => '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/download_errors/*',
                    'method' => 'GET',
                    'auth' => true,
                ],
            ],
        ],

    ],
];
