<?php
declare(strict_types=1);

namespace BcCsvImportSampleOrderDetails\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BcCsvSampleOrderDetailsTable（受注明細）
 */
class BcCsvSampleOrderDetailsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('bc_csv_sample_order_details');
        $this->setDisplayField('product_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Orders', [
            'className'  => 'BcCsvImportSampleOrderDetails.BcCsvSampleOrders',
            'foreignKey' => 'order_id',
        ]); // BcCsvSampleOrdersTable が bc_csv_sample_order_headers を指す
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('order_id')
            ->requirePresence('order_id', 'create')
            ->notEmptyString('order_id', __d('baser_core', '受注IDは必須です。'));

        $validator
            ->scalar('order_no')
            ->maxLength('order_no', 50, __d('baser_core', '受注番号は50文字以内で入力してください。'))
            ->requirePresence('order_no', 'create')
            ->notEmptyString('order_no', __d('baser_core', '受注番号は必須です。'));

        $validator
            ->scalar('product_sku')
            ->maxLength('product_sku', 100, __d('baser_core', 'SKUは100文字以内で入力してください。'))
            ->allowEmptyString('product_sku');

        $validator
            ->scalar('product_name')
            ->maxLength('product_name', 255, __d('baser_core', '商品名は255文字以内で入力してください。'))
            ->requirePresence('product_name', 'create')
            ->notEmptyString('product_name', __d('baser_core', '商品名は必須です。'));

        $validator
            ->integer('quantity')
            ->greaterThan('quantity', 0, __d('baser_core', '数量は1以上の数値を入力してください。'))
            ->allowEmptyString('quantity');

        $validator
            ->integer('unit_price')
            ->greaterThanOrEqual('unit_price', 0, __d('baser_core', '単価は0以上の数値を入力してください。'))
            ->allowEmptyString('unit_price');

        $validator
            ->integer('line_total')
            ->greaterThanOrEqual('line_total', 0, __d('baser_core', '明細小計は0以上の数値を入力してください。'))
            ->allowEmptyString('line_total');

        return $validator;
    }
}
