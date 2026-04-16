<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Models;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Class PaymentTransactions.
 *
 * @package OnlinePayments\Models
 */
class PaymentTransactions extends BaseEntity
{
    public static function tableName(): string
    {
        return ModuleHelper::getFullTableName('payment_transactions');
    }
}
