<?php
declare(strict_types=1);

namespace BcCsvImportSampleOrderDetails\Service;

use BcCsvImportCore\Service\CsvImportService;
use BcCsvImportCore\Service\CsvImportServiceInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use DateTime;

/**
 * SampleOrderDetailsCsvImportService
 *
 * 1対多CSVインポートのサンプル実装。
 * 1行に受注ヘッダー＋明細が混在するCSVを、
 * bc_csv_sample_order_headers（受注）と bc_csv_sample_order_details（明細）の2テーブルに分けて保存する。
 *
 * 同一バッチ内で同じ受注番号が複数行あると受注ヘッダーを共有し、
 * バッチをまたぐ場合も既存の受注レコードに明細を追記する。
 *
 * CSVフォーマット（1行1明細）:
 * 受注番号, 顧客名, メールアドレス, 電話番号, ステータス, 受注日時, 商品コード, 商品名, 数量, 単価
 */
class SampleOrderDetailsCsvImportService extends CsvImportService implements CsvImportServiceInterface
{

    /** @var Table 受注ヘッダーテーブル */
    private Table $ordersTable;

    /** @var Table 受注明細テーブル */
    private Table $detailsTable;

    public function __construct()
    {
        parent::__construct();
        $this->ordersTable  = TableRegistry::getTableLocator()->get('BcCsvImportSampleOrderDetails.BcCsvSampleOrders');
        $this->detailsTable = TableRegistry::getTableLocator()->get('BcCsvImportSampleOrderDetails.BcCsvSampleOrderDetails');
    }

    /**
     * インポート対象のテーブル名（AdminService が参照するテーブル）
     *
     * @return string
     */
    public function getTableName(): string
    {
        return 'BcCsvImportSampleOrderDetails.BcCsvSampleOrders';
    }

    /**
     * CSVカラムマップ（受注ヘッダー＋明細の全カラム）
     *
     * @return array
     */
    public function getColumnMap(): array
    {
        return [
            'order_no'       => ['label' => '受注番号',       'required' => true,  'sample' => 'ORD-20260401-001'],
            'customer_name'  => ['label' => '顧客名',         'required' => true,  'sample' => '山田 太郎'],
            'customer_email' => ['label' => 'メールアドレス', 'required' => false, 'sample' => 'taro@example.com'],
            'customer_tel'   => ['label' => '電話番号',       'required' => false, 'sample' => '03-1234-5678'],
            'status'         => ['label' => 'ステータス',     'required' => false, 'sample' => 'new'],
            'ordered_at'     => ['label' => '受注日時',       'required' => false, 'sample' => '2026-04-01 10:00:00'],
            'product_sku'    => ['label' => '商品コード',     'required' => false, 'sample' => 'SKU-A'],
            'product_name'   => ['label' => '商品名',         'required' => true,  'sample' => '商品A'],
            'quantity'       => ['label' => '数量',           'required' => false, 'sample' => '2'],
            'unit_price'     => ['label' => '単価',           'required' => false, 'sample' => '1000'],
        ];
    }

    /**
     * 重複チェックキー（processBatch をオーバーライドするため未使用）
     *
     * @return string
     */
    public function getDuplicateKey(): string
    {
        return '';
    }

    /**
     * バリデーション用エンティティ生成（validateBatch のオーバーライドで使用されないが実装必須）
     *
     * @param array $row
     * @return EntityInterface
     */
    public function buildEntity(array $row): EntityInterface
    {
        $nullIfEmpty   = fn(?string $v): ?string => ($v !== null && $v !== '') ? trim($v) : null;
        $intIfNotEmpty = fn(?string $v): ?int    => ($v !== null && $v !== '') ? (int)$v : null;

        return $this->detailsTable->newEntity([
            'order_no'     => $nullIfEmpty($row['order_no'] ?? null),
            'product_sku'  => $nullIfEmpty($row['product_sku'] ?? null),
            'product_name' => $nullIfEmpty($row['product_name'] ?? null),
            'quantity'     => $intIfNotEmpty($row['quantity'] ?? null),
            'unit_price'   => $intIfNotEmpty($row['unit_price'] ?? null),
        ]);
    }

    /**
     * バリデーションバッチ（受注ヘッダー＋明細の両方をバリデート）
     *
     * @param string $token
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function validateBatch(string $token, int $offset, int $limit): array
    {
        $job       = $this->getJobByToken($token);
        $columnMap = $this->getColumnMap();
        $headers   = array_keys($columnMap);

        if ($job->status === 'cancelled') {
            return [
                'token'           => $token,
                'processed'       => (int)$job->processed,
                'total'           => (int)$job->total,
                'error_count'     => (int)$job->error_count,
                'batch_errors'    => [],
                'phase_completed' => true,
            ];
        }

        if ($job->status === 'pending') {
            $job = $this->Jobs->patchEntity($job, ['status' => 'processing', 'started_at' => new DateTime()]);
            $this->Jobs->saveOrFail($job);
        }

        $batch         = $this->readCsvBatchByPosition($job->csv_path, (int)($job->validate_position ?? 0), $limit);
        $rows          = $batch['rows'];
        $batchErrors   = [];
        $baseProcessed = (int)$job->processed;

        $nullIfEmpty   = fn(?string $v): ?string => ($v !== null && $v !== '') ? trim($v) : null;
        $intIfNotEmpty = fn(?string $v): ?int    => ($v !== null && $v !== '') ? (int)$v : null;

        foreach ($rows as $i => $row) {
            $rowNumber = $baseProcessed + $i + 2;
            $data      = array_combine($headers, array_pad($row, count($headers), null));

            // 受注ヘッダーのバリデーション
            $orderEntity = $this->ordersTable->newEntity([
                'order_no'       => $nullIfEmpty($data['order_no'] ?? null),
                'customer_name'  => $nullIfEmpty($data['customer_name'] ?? null),
                'customer_email' => $nullIfEmpty($data['customer_email'] ?? null),
                'customer_tel'   => $nullIfEmpty($data['customer_tel'] ?? null),
                'status'         => $nullIfEmpty($data['status'] ?? null) ?? 'new',
                'ordered_at'     => $nullIfEmpty($data['ordered_at'] ?? null),
            ]);

            foreach ($orderEntity->getErrors() as $field => $fieldErrors) {
                foreach ($fieldErrors as $message) {
                    $batchErrors[] = [
                        'row'     => $rowNumber,
                        'field'   => $field,
                        'label'   => $columnMap[$field]['label'] ?? $field,
                        'message' => $message,
                        'data'    => $row,
                    ];
                }
            }

            // 明細のバリデーション（order_id は保存時に決まるためスキップ）
            $detailEntity = $this->detailsTable->newEntity([
                'order_id'     => 1, // バリデーション用ダミー値
                'order_no'     => $nullIfEmpty($data['order_no'] ?? null),
                'product_sku'  => $nullIfEmpty($data['product_sku'] ?? null),
                'product_name' => $nullIfEmpty($data['product_name'] ?? null),
                'quantity'     => $intIfNotEmpty($data['quantity'] ?? null),
                'unit_price'   => $intIfNotEmpty($data['unit_price'] ?? null),
                'line_total'   => null,
            ]);

            foreach ($detailEntity->getErrors() as $field => $fieldErrors) {
                if ($field === 'order_id') {
                    continue; // ダミー値なのでスキップ
                }
                foreach ($fieldErrors as $message) {
                    $batchErrors[] = [
                        'row'     => $rowNumber,
                        'field'   => $field,
                        'label'   => $columnMap[$field]['label'] ?? $field,
                        'message' => $message,
                        'data'    => $row,
                    ];
                }
            }
        }

        $this->appendErrorsToLog($job, $batchErrors);

        $processed       = $baseProcessed + count($rows);
        $totalErrorCount = (int)$job->error_count + count($batchErrors);
        $phaseCompleted  = $processed >= (int)$job->total || count($rows) === 0;

        $updateData = [
            'processed'         => $processed,
            'error_count'       => $totalErrorCount,
            'validate_position' => $batch['next_position'],
        ];

        if ($phaseCompleted) {
            if ($totalErrorCount > 0) {
                $updateData['status']   = 'failed';
                $updateData['ended_at'] = new DateTime();
                $updateData['phase']    = 'validate';
            } else {
                $updateData['phase']           = 'import';
                $updateData['processed']       = 0;
                $updateData['import_position'] = 0;
            }
        }

        $job = $this->Jobs->patchEntity($job, $updateData);
        $this->Jobs->saveOrFail($job);

        return [
            'token'           => $token,
            'processed'       => $phaseCompleted ? (int)$job->total : $processed,
            'total'           => $job->total,
            'error_count'     => $totalErrorCount,
            'batch_errors'    => $batchErrors,
            'phase_completed' => $phaseCompleted,
        ];
    }

    /**
     * インポートバッチ（order_no でグループ化して受注＋明細を保存）
     *
     * 同一バッチ内の同じ受注番号を1つの受注として処理する。
     * バッチをまたぐ場合も order_no で既存受注を検索して明細を追記する。
     *
     * @param string $token
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function processBatch(string $token, int $offset, int $limit): array
    {
        $job       = $this->getJobByToken($token);
        $columnMap = $this->getColumnMap();
        $headers   = array_keys($columnMap);

        if ($job->status === 'cancelled') {
            return [
                'token'         => $token,
                'processed'     => (int)$job->processed,
                'total'         => (int)$job->total,
                'success_count' => (int)$job->success_count,
                'skip_count'    => (int)$job->skip_count,
                'error_count'   => (int)$job->error_count,
                'completed'     => true,
            ];
        }

        if ($job->status === 'pending') {
            $job = $this->Jobs->patchEntity($job, ['status' => 'processing', 'started_at' => new DateTime(), 'phase' => 'import']);
            $this->Jobs->saveOrFail($job);
        } elseif ($job->phase !== 'import') {
            $job = $this->Jobs->patchEntity($job, ['phase' => 'import']);
            $this->Jobs->saveOrFail($job);
        }

        // replace strategy: 明細 → 受注の順に全削除
        if ($job->import_strategy === 'replace' && !$job->target_cleared) {
            $connection = $this->ordersTable->getConnection();
            $connection->begin();
            try {
                $this->detailsTable->deleteAll([]);
                $this->ordersTable->deleteAll([]);
                $connection->commit();
            } catch (\Throwable $e) {
                $connection->rollback();
                throw $e;
            }
            $job = $this->Jobs->patchEntity($job, ['target_cleared' => true]);
            $this->Jobs->saveOrFail($job);
            $job = $this->getJobByToken($token);
        }

        $batch         = $this->readCsvBatchByPosition($job->csv_path, (int)($job->import_position ?? 0), $limit);
        $rows          = $batch['rows'];
        $successCount  = 0;
        $skipCount     = 0;
        $batchErrors   = [];
        $baseProcessed = (int)$job->processed;

        $nullIfEmpty   = fn(?string $v): ?string => ($v !== null && $v !== '') ? trim($v) : null;
        $intIfNotEmpty = fn(?string $v): ?int    => ($v !== null && $v !== '') ? (int)$v : null;

        // ── バッチ内の行を order_no でグループ化 ──────────────────────────────
        // $groups   = ['ORD-001' => ['orderData' => [...], 'details' => [...]]]
        // $groupOrder = ['ORD-001', 'ORD-002', ...]（出現順）
        $groups     = [];
        $groupOrder = [];

        foreach ($rows as $i => $row) {
            $data    = array_combine($headers, array_pad($row, count($headers), null));
            $orderNo = $nullIfEmpty($data['order_no'] ?? null);

            if ($orderNo === null) {
                $batchErrors[] = [
                    'row'     => $baseProcessed + $i + 2,
                    'field'   => 'order_no',
                    'label'   => '受注番号',
                    'message' => '受注番号は必須です。',
                    'data'    => $row,
                ];
                $skipCount++;
                continue;
            }

            if (!isset($groups[$orderNo])) {
                $groups[$orderNo] = [
                    'orderData' => [
                        'order_no'       => $orderNo,
                        'customer_name'  => $nullIfEmpty($data['customer_name'] ?? null),
                        'customer_email' => $nullIfEmpty($data['customer_email'] ?? null),
                        'customer_tel'   => $nullIfEmpty($data['customer_tel'] ?? null),
                        'status'         => $nullIfEmpty($data['status'] ?? null) ?? 'new',
                        'ordered_at'     => $nullIfEmpty($data['ordered_at'] ?? null),
                    ],
                    'details' => [],
                ];
                $groupOrder[] = $orderNo;
            }

            $qty        = $intIfNotEmpty($data['quantity'] ?? null);
            $price      = $intIfNotEmpty($data['unit_price'] ?? null);
            $lineTotal  = ($qty !== null && $price !== null) ? $qty * $price : null;

            $groups[$orderNo]['details'][] = [
                'rowNumber'  => $baseProcessed + $i + 2,
                'rawRow'     => $row,
                'detailData' => [
                    'order_no'     => $orderNo,
                    'product_sku'  => $nullIfEmpty($data['product_sku'] ?? null),
                    'product_name' => $nullIfEmpty($data['product_name'] ?? null),
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'line_total'   => $lineTotal,
                ],
            ];
        }

        // ── グループごとに受注＋明細を保存 ────────────────────────────────────
        $connection = $this->ordersTable->getConnection();
        $connection->begin();

        try {
            foreach ($groupOrder as $orderNo) {
                $group = $groups[$orderNo];

                // 受注ヘッダーの UPSERT（order_no で検索）
                /** @var \BcCsvImportSampleOrderDetails\Model\Entity\BcCsvSampleOrder|null $existingOrder */
                $existingOrder = $this->ordersTable->find()
                    ->where(['order_no' => $orderNo])
                    ->first();

                if ($existingOrder) {
                    switch ($job->duplicate_mode) {
                        case 'overwrite':
                            $existingOrder = $this->ordersTable->patchEntity(
                                $existingOrder,
                                $group['orderData'],
                                ['validate' => false]
                            );
                            $this->ordersTable->saveOrFail($existingOrder);
                            $order = $existingOrder;
                            break;
                        case 'error':
                            // 受注ヘッダーはエラーとして報告しつつ、明細は追記を試みる
                            $batchErrors[] = [
                                'row'     => $group['details'][0]['rowNumber'] ?? 0,
                                'field'   => 'order_no',
                                'label'   => '受注番号',
                                'message' => __d('baser_core', '同じ受注番号の受注が既に存在します。'),
                                'data'    => $group['details'][0]['rawRow'] ?? [],
                            ];
                            $order = $existingOrder;
                            break;
                        case 'skip':
                        default:
                            // 受注ヘッダーはスキップ（既存を使用）、明細は追記
                            $order = $existingOrder;
                            break;
                    }
                } else {
                    $order = $this->ordersTable->newEntity($group['orderData'], ['validate' => false]);
                    $this->ordersTable->saveOrFail($order);
                }

                // 明細の保存
                foreach ($group['details'] as $detailInfo) {
                    $detailData = array_merge($detailInfo['detailData'], ['order_id' => $order->id]);
                    $productSku = $detailData['product_sku'];

                    // 明細の重複チェック（order_id + product_sku）
                    $existingDetail = null;
                    if ($productSku !== null) {
                        $existingDetail = $this->detailsTable->find()
                            ->where(['order_id' => $order->id, 'product_sku' => $productSku])
                            ->first();
                    }

                    if ($existingDetail) {
                        switch ($job->duplicate_mode) {
                            case 'overwrite':
                                $detail = $this->detailsTable->patchEntity(
                                    $existingDetail,
                                    $detailData,
                                    ['validate' => false]
                                );
                                break;
                            case 'error':
                                $batchErrors[] = [
                                    'row'     => $detailInfo['rowNumber'],
                                    'field'   => 'product_sku',
                                    'label'   => '商品コード',
                                    'message' => __d('baser_core', '同じ受注番号・商品コードの明細が既に存在します。'),
                                    'data'    => $detailInfo['rawRow'],
                                ];
                                $skipCount++;
                                continue 2;
                            case 'skip':
                            default:
                                $skipCount++;
                                continue 2;
                        }
                    } else {
                        $detail = $this->detailsTable->newEntity($detailData, ['validate' => false]);
                    }

                    if ($this->detailsTable->save($detail, ['checkRules' => false, 'validate' => false])) {
                        $successCount++;
                    } else {
                        $skipCount++;
                    }
                }
            }

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollback();
            throw $e;
        }

        $this->appendErrorsToLog($job, $batchErrors);

        $processed   = $baseProcessed + count($rows);
        $totalSuccess = ($job->success_count ?? 0) + $successCount;
        $totalSkip    = ($job->skip_count ?? 0) + $skipCount;
        $totalErrors  = ($job->error_count ?? 0) + count($batchErrors);
        $completed    = $processed >= (int)$job->total || count($rows) === 0;

        $updateData = [
            'processed'       => $processed,
            'success_count'   => $totalSuccess,
            'skip_count'      => $totalSkip,
            'error_count'     => $totalErrors,
            'import_position' => $batch['next_position'],
            'phase'           => 'import',
        ];

        if ($completed) {
            $updateData['status']   = 'completed';
            $updateData['ended_at'] = new DateTime();
        }

        $job = $this->Jobs->patchEntity($job, $updateData);
        $this->Jobs->saveOrFail($job);

        return [
            'token'         => $token,
            'processed'     => $completed ? (int)$job->total : $processed,
            'total'         => $job->total,
            'success_count' => $totalSuccess,
            'skip_count'    => $totalSkip,
            'error_count'   => $totalErrors,
            'completed'     => $completed,
            'batch_errors'  => array_slice($batchErrors, 0, 200),
        ];
    }
}
