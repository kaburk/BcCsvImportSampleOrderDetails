<?php
declare(strict_types=1);

namespace BcCsvImportSampleOrderDetails\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BcCsvSampleOrdersTable（受注ヘッダー）
 */
class BcCsvSampleOrdersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('bc_csv_sample_order_headers');
        $this->setDisplayField('order_no');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Details', [
            'className'  => 'BcCsvImportSampleOrderDetails.BcCsvSampleOrderDetails',
            'foreignKey' => 'order_id',
            'dependent'  => true,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('order_no')
            ->maxLength('order_no', 50)
            ->requirePresence('order_no', 'create')
            ->notEmptyString('order_no');

        $validator
            ->scalar('customer_name')
            ->maxLength('customer_name', 255)
            ->requirePresence('customer_name', 'create')
            ->notEmptyString('customer_name');

        $validator
            ->email('customer_email')
            ->maxLength('customer_email', 255)
            ->allowEmptyString('customer_email');

        $validator
            ->scalar('customer_tel')
            ->maxLength('customer_tel', 30)
            ->allowEmptyString('customer_tel');

        $validator
            ->scalar('status')
            ->maxLength('status', 30)
            ->allowEmptyString('status');

        $validator
            ->dateTime('ordered_at')
            ->allowEmptyDateTime('ordered_at');

        return $validator;
    }
}
