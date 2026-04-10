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
            ->maxLength('order_no', 50, __d('baser_core', '受注番号は50文字以内で入力してください。'))
            ->requirePresence('order_no', 'create')
            ->notEmptyString('order_no', __d('baser_core', '受注番号は必須です。'));

        $validator
            ->scalar('customer_name')
            ->maxLength('customer_name', 255, __d('baser_core', '顧客名は255文字以内で入力してください。'))
            ->requirePresence('customer_name', 'create')
            ->notEmptyString('customer_name', __d('baser_core', '顧客名は必須です。'));

        $validator
            ->email('customer_email', false, __d('baser_core', 'メールアドレスの形式が正しくありません。'))
            ->maxLength('customer_email', 255, __d('baser_core', 'メールアドレスは255文字以内で入力してください。'))
            ->allowEmptyString('customer_email');

        $validator
            ->scalar('customer_tel')
            ->maxLength('customer_tel', 30, __d('baser_core', '電話番号は30文字以内で入力してください。'))
            ->allowEmptyString('customer_tel');

        $validator
            ->scalar('status')
            ->maxLength('status', 30, __d('baser_core', 'ステータスは30文字以内で入力してください。'))
            ->allowEmptyString('status');

        $validator
            ->dateTime('ordered_at')
            ->allowEmptyDateTime('ordered_at');

        return $validator;
    }
}
