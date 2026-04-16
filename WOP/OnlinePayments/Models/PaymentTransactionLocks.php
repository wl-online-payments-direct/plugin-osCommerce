<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Models;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Class PaymentTransactionLocks.
 *
 * @package OnlinePayments\Models
 */
class PaymentTransactionLocks extends BaseEntity
{
    public static function tableName(): string
    {
        return ModuleHelper::getFullTableName('payment_transaction_locks');
    }
}
