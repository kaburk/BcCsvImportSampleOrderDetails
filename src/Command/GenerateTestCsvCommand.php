<?php
declare(strict_types=1);

namespace BcCsvImportSampleOrderDetails\Command;

use BcCsvImportCore\Command\AbstractGenerateTestCsvCommand;
use BcCsvImportCore\Service\CsvImportServiceInterface;
use BcCsvImportSampleOrderDetails\Service\SampleOrderDetailsCsvImportService;

/**
 * GenerateTestCsvCommand（受注明細1対多）
 *
 * 1対多CSVのテストデータを生成する。
 * 1受注につき2〜4明細行を出力し、同一受注番号の行が連続するフォーマットにする。
 *
 * 使い方:
 *   bin/cake BcCsvImportSampleOrderDetails.generate_test_csv
 *   bin/cake BcCsvImportSampleOrderDetails.generate_test_csv --sizes=10 --errors=2
 */
class GenerateTestCsvCommand extends AbstractGenerateTestCsvCommand
{

    /** 1受注あたりの最大明細数 */
    private const MAX_DETAILS_PER_ORDER = 4;

    public static function defaultName(): string
    {
        return 'bc_csv_import_sample_order_details.generate_test_csv';
    }

    protected function getCommandDescription(): string
    {
        return '受注明細1対多テスト用CSVファイルを生成します。(--sizes で明細行数を指定)';
    }

    protected function getService(): CsvImportServiceInterface
    {
        return new SampleOrderDetailsCsvImportService();
    }

    protected function getFilenamePrefix(): string
    {
        return 'import_sample_order_details_';
    }

    /**
     * 1行（1明細）のデータを生成する
     *
     * 行番号 $i は「明細の通し番号」。
     * 同一受注番号の行が連続するよう、受注番号は MAX_DETAILS_PER_ORDER 行ごとに変わる。
     *
     * @param int $i 1始まりの行番号（明細通し番号）
     * @param array $columnKeys カラムキー配列
     * @return array
     */
    protected function buildRow(int $i, array $columnKeys): array
    {
        $detailIndex  = ($i - 1) % self::MAX_DETAILS_PER_ORDER; // 0〜3
        $orderIndex   = (int)(($i - 1) / self::MAX_DETAILS_PER_ORDER) + 1;

        $statuses     = ['new', 'processing', 'shipped', 'cancelled'];
        $skus         = ['SKU-A', 'SKU-B', 'SKU-C', 'SKU-D'];
        $products     = ['商品A テスト用', '商品B テスト用', '商品C テスト用', '商品D テスト用'];
        $baseDate     = new \DateTimeImmutable('2026-01-01 10:00:00');
        $orderedAt    = $baseDate->modify('+' . ($orderIndex - 1) . ' days')->format('Y-m-d H:i:s');
        $qty          = ($detailIndex % 3) + 1;
        $unitPrice    = (($detailIndex + 1) * 500) + ($orderIndex % 10) * 100;

        $row = [];
        foreach ($columnKeys as $key) {
            $row[$key] = match ($key) {
                'order_no'       => sprintf('ORD-%06d', $orderIndex),
                'customer_name'  => 'テスト顧客' . $orderIndex,
                'customer_email' => 'test' . $orderIndex . '@example.com',
                'customer_tel'   => sprintf('03-%04d-%04d', $orderIndex % 10000, ($orderIndex + 1) % 10000),
                'status'         => $statuses[$orderIndex % count($statuses)],
                'ordered_at'     => $orderedAt,
                'product_sku'    => $skus[$detailIndex],
                'product_name'   => $products[$detailIndex],
                'quantity'       => $qty,
                'unit_price'     => $unitPrice,
                default          => '',
            };
        }

        return $row;
    }

    protected function getErrorPatterns(): array
    {
        return [
            '受注番号が空（必須項目エラー）' => function (array $row): array {
                $row['order_no'] = '';
                return $row;
            },
            '顧客名が空（必須項目エラー）' => function (array $row): array {
                $row['customer_name'] = '';
                return $row;
            },
            '商品名が空（必須項目エラー）' => function (array $row): array {
                $row['product_name'] = '';
                return $row;
            },
            '数量が0（バリデーションエラー）' => function (array $row): array {
                $row['quantity'] = 0;
                return $row;
            },
            '単価が負の値（バリデーションエラー）' => function (array $row): array {
                $row['unit_price'] = -100;
                return $row;
            },
        ];
    }
}
