<?php
declare(strict_types=1);

use BaserCore\Database\Migration\BcMigration;

/**
 * 受注明細テーブル bc_csv_sample_order_details 作成
 */
class CreateBcOrderDetailDetails extends BcMigration
{
    public function up()
    {
        if ($this->hasTable('bc_csv_sample_order_details')) {
            return;
        }

        $this->table('bc_csv_sample_order_details', ['collation' => 'utf8mb4_general_ci'])
            ->addColumn('order_id', 'integer', ['null' => false, 'comment' => '受注ID（bc_csv_sample_order_headers.id）'])
            ->addColumn('order_no', 'string', ['limit' => 50, 'null' => false, 'comment' => '受注番号（参照用）'])
            ->addColumn('product_sku', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => '商品コード'])
            ->addColumn('product_name', 'string', ['limit' => 255, 'null' => false, 'comment' => '商品名'])
            ->addColumn('quantity', 'integer', ['null' => true, 'default' => null, 'comment' => '数量'])
            ->addColumn('unit_price', 'integer', ['null' => true, 'default' => null, 'comment' => '単価'])
            ->addColumn('line_total', 'integer', ['null' => true, 'default' => null, 'comment' => '行合計'])
            ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['order_id'])
            ->addIndex(['order_id', 'product_sku'])
            ->create();
    }

    public function down()
    {
        if (!$this->hasTable('bc_csv_sample_order_details')) {
            return;
        }

        $this->table('bc_csv_sample_order_details')->drop()->save();
    }
}
