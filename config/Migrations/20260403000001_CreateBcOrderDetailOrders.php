<?php
declare(strict_types=1);

use BaserCore\Database\Migration\BcMigration;

/**
 * 受注ヘッダーテーブル bc_csv_sample_order_headers 作成
 */
class CreateBcOrderDetailOrders extends BcMigration
{
    public function up()
    {
        if ($this->hasTable('bc_csv_sample_order_headers')) {
            return;
        }

        $this->table('bc_csv_sample_order_headers', ['collation' => 'utf8mb4_general_ci'])
            ->addColumn('order_no', 'string', ['limit' => 50, 'null' => false, 'comment' => '受注番号'])
            ->addColumn('customer_name', 'string', ['limit' => 255, 'null' => false, 'comment' => '顧客名'])
            ->addColumn('customer_email', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => 'メールアドレス'])
            ->addColumn('customer_tel', 'string', ['limit' => 30, 'null' => true, 'default' => null, 'comment' => '電話番号'])
            ->addColumn('status', 'string', ['limit' => 30, 'null' => true, 'default' => 'new', 'comment' => 'ステータス'])
            ->addColumn('ordered_at', 'datetime', ['null' => true, 'default' => null, 'comment' => '受注日時'])
            ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['order_no'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        if (!$this->hasTable('bc_csv_sample_order_headers')) {
            return;
        }

        $this->table('bc_csv_sample_order_headers')->drop()->save();
    }
}
