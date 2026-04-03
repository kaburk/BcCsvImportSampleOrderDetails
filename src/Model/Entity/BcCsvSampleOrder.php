<?php
declare(strict_types=1);

namespace BcCsvImportSampleOrderDetails\Model\Entity;

use Cake\ORM\Entity;

/**
 * BcCsvSampleOrder Entity（受注ヘッダー）
 *
 * @property int $id
 * @property string $order_no
 * @property string $customer_name
 * @property string|null $customer_email
 * @property string|null $customer_tel
 * @property string|null $status
 * @property \Cake\I18n\DateTime|null $ordered_at
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \BcCsvImportSampleOrderDetails\Model\Entity\BcCsvSampleOrderDetail[] $details
 */
class BcCsvSampleOrder extends Entity
{
    protected array $_accessible = [
        'order_no'       => true,
        'customer_name'  => true,
        'customer_email' => true,
        'customer_tel'   => true,
        'status'         => true,
        'ordered_at'     => true,
        'created'        => true,
        'modified'       => true,
        'details'        => true,
    ];
}
