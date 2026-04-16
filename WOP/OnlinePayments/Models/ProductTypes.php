<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Models;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Class ProductType.
 *
 * @package OnlinePayments\Models
 */
class ProductTypes extends BaseEntity
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return ModuleHelper::getFullTableName('product_types');
    }
}
