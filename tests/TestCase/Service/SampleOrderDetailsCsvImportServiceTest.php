<?php
declare(strict_types=1);

namespace BcCsvImportSampleOrderDetails\Test\TestCase\Service;

use BaserCore\TestSuite\BcTestCase;
use BcCsvImportSampleOrderDetails\Service\SampleOrderDetailsCsvImportService;

/**
 * SampleOrderDetailsCsvImportServiceTest
 *
 * SampleOrderDetailsCsvImportService のカラムマップ・エンティティ構築を検証する。
 * 1対多構造（受注ヘッダー＋明細）の特性を考慮したテスト。
 */
class SampleOrderDetailsCsvImportServiceTest extends BcTestCase
{
    /** @var SampleOrderDetailsCsvImportService */
    private SampleOrderDetailsCsvImportService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new SampleOrderDetailsCsvImportService();
    }

    // ─────────────────────────────────────────────────────────────
    // getColumnMap
    // ─────────────────────────────────────────────────────────────

    public function testGetColumnMapReturnsExpectedKeys(): void
    {
        $map = $this->service->getColumnMap();

        $expectedKeys = [
            'order_no', 'customer_name', 'customer_email', 'customer_tel',
            'status', 'ordered_at', 'product_sku', 'product_name', 'quantity', 'unit_price',
        ];
        $this->assertSame($expectedKeys, array_keys($map));
    }

    public function testGetColumnMapRequiredFields(): void
    {
        $map = $this->service->getColumnMap();

        $this->assertTrue($map['order_no']['required'] ?? false, "'order_no' は required であるべき");
        $this->assertTrue($map['customer_name']['required'] ?? false, "'customer_name' は required であるべき");
        $this->assertTrue($map['product_name']['required'] ?? false, "'product_name' は required であるべき");
    }

    // ─────────────────────────────────────────────────────────────
    // getDuplicateKey（1対多のため未使用→空文字）
    // ─────────────────────────────────────────────────────────────

    public function testGetDuplicateKeyReturnsEmpty(): void
    {
        $this->assertSame('', $this->service->getDuplicateKey(), '1対多構造では重複キーは未使用');
    }

    // ─────────────────────────────────────────────────────────────
    // buildTemplateCsv
    // ─────────────────────────────────────────────────────────────

    public function testBuildTemplateCsvContainsAllLabels(): void
    {
        $csv    = $this->service->buildTemplateCsv();
        $lines  = array_filter(explode("\n", trim($csv)));
        $header = str_getcsv(array_values($lines)[0]);

        $expectedLabels = array_values(array_map(fn($v) => $v['label'], $this->service->getColumnMap()));
        $this->assertSame($expectedLabels, $header);
    }

    // ─────────────────────────────────────────────────────────────
    // getTableName
    // ─────────────────────────────────────────────────────────────

    public function testGetTableNameReturnsOrdersTable(): void
    {
        $this->assertStringContainsString(
            'BcCsvSampleOrders',
            $this->service->getTableName(),
            '受注ヘッダーテーブルが返されること'
        );
    }
}
