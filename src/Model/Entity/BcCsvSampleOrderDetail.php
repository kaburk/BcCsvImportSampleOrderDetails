<?php
declare(strict_types=1);

namespace BcCsvImportSampleOrderDetails\Model\Entity;

use Cake\ORM\Entity;

/**
 * BcCsvSampleOrderDetail Entity（受注明細）
 *
 * @property int $id
 * @property int $order_id
 * @property string $order_no
 * @property string|null $product_sku
 * @property string $product_name
 * @property int|null $quantity
 * @property int|null $unit_price
 * @property int|null $line_total
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class BcCsvSampleOrderDetail extends Entity
{
    protected array $_accessible = [
        'order_id'     => true,
        'order_no'     => true,
        'product_sku'  => true,
        'product_name' => true,
        'quantity'     => true,
        'unit_price'   => true,
        'line_total'   => true,
        'created'      => true,
        'modified'     => true,
    ];
}
