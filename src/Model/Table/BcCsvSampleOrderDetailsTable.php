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
            ->notEmptyString('order_id');

        $validator
            ->scalar('order_no')
            ->maxLength('order_no', 50)
            ->requirePresence('order_no', 'create')
            ->notEmptyString('order_no');

        $validator
            ->scalar('product_sku')
            ->maxLength('product_sku', 100)
            ->allowEmptyString('product_sku');

        $validator
            ->scalar('product_name')
            ->maxLength('product_name', 255)
            ->requirePresence('product_name', 'create')
            ->notEmptyString('product_name');

        $validator
            ->integer('quantity')
            ->greaterThan('quantity', 0)
            ->allowEmptyString('quantity');

        $validator
            ->integer('unit_price')
            ->greaterThanOrEqual('unit_price', 0)
            ->allowEmptyString('unit_price');

        $validator
            ->integer('line_total')
            ->greaterThanOrEqual('line_total', 0)
            ->allowEmptyString('line_total');

        return $validator;
    }
}
