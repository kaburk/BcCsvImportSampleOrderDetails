# BcCsvImportSampleOrderDetails

`BcCsvImportSampleOrderDetails` は、`BcCsvImportCore` を使った 1対多 CSV インポートのサンプルプラグインです。
1行1明細の CSV を受け取り、受注ヘッダーを `bc_csv_sample_order_headers`、受注明細を `bc_csv_sample_order_details` に分けて保存します。

## 用途

- 1対多インポート実装のサンプル
- `order_no` で受注ヘッダーをまとめ、複数明細を保存する実装例
- 実案件向けの受注明細インポートプラグイン作成時の土台

## 前提

- `BcCsvImportCore` を有効化済みであること
- 本プラグインを有効化すると、サンプル用の 2 テーブルの migration が実行されます。
- この migration は動作確認用です。実運用では受注管理側のテーブルを利用する想定です。

## 管理画面

- メニュー名: `受注明細CSVインポート サンプル`
- URL: `/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports/index`

画面構成自体は `BcCsvImportCore` の共通UIです。

この画面でダウンロードできるテンプレートCSVは、1行1明細の列順に合わせて動的生成されます。

## 対象テーブル

- 受注ヘッダー Model alias: `BcCsvImportSampleOrderDetails.BcCsvSampleOrders`
- 受注ヘッダー物理テーブル: `bc_csv_sample_order_headers`
- 受注明細 Model alias: `BcCsvImportSampleOrderDetails.BcCsvSampleOrderDetails`
- 受注明細物理テーブル: `bc_csv_sample_order_details`
- 受注ヘッダー重複キー: `order_no`

## CSVフォーマット

テンプレートCSVのヘッダは次の通りです。

```csv
受注番号,顧客名,メールアドレス,電話番号,ステータス,受注日時,商品コード,商品名,数量,単価
```

1 行が 1 明細を表し、同じ `受注番号` の行が連続していても問題ありません。
インポート時に `order_no` ごとにグループ化し、受注ヘッダーを 1 件、明細を複数件として保存します。

## 実装の見どころ

- サービス実装: `src/Service/SampleOrderDetailsCsvImportService.php`
- Table / Entity:
  - `src/Model/Table/BcCsvSampleOrdersTable.php`
  - `src/Model/Table/BcCsvSampleOrderDetailsTable.php`
  - `src/Model/Entity/BcCsvSampleOrder.php`
  - `src/Model/Entity/BcCsvSampleOrderDetail.php`
- 専用コントローラー: `src/Controller/Admin/SampleOrderDetailsCsvImportsController.php`
- 画面テンプレート: `BcCsvImportCore` の共通テンプレート `Admin/CsvImports/index` を再利用

## テストデータ生成

大量件数で挙動確認したい場合は、CakePHP コンソールコマンドでテスト用 CSV を生成できます。

```bash
bin/cake BcCsvImportSampleOrderDetails.generate_test_csv
```

生成ファイル名は `import_sample_order_details_*.csv` です。
例: `--sizes=10k --errors=5` の場合は `import_sample_order_details_10k_err5pct.csv` が生成されます。

このプラグインの `--sizes` は受注件数ではなく、CSVの明細行数を意味します。
`MAX_DETAILS_PER_ORDER = 4` のため、`--sizes=8` なら最大で 2 受注分の明細CSVになります。

主なオプション:

- `--output=/path/to/dir` 出力先ディレクトリを変更（デフォルト: `tmp/csv/`）
- `--sizes=8,10k` 生成件数をカンマ区切りで指定（デフォルト: `10k`）
- `--errors=5` エラー行を約 5% 含める（デフォルト: `0`）

エラー行は一定間隔で差し込まれ、必須項目欠落や不正単価などのパターンを確認できます。

ヘルプを表示するには:

```bash
bin/cake BcCsvImportSampleOrderDetails.generate_test_csv --help
```

## ライセンス

MIT License. 詳細は `LICENSE.txt` を参照してください。
