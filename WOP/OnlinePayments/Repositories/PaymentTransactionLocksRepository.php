<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Models\PaymentTransactionLocks;
/**
 * Class PaymentTransactionLocksRepository.
 *
 * @package OnlinePayments\Repositories
 */
class PaymentTransactionLocksRepository extends BaseRepositoryWithConditionalDelete
{
    public const THIS_CLASS_NAME = __CLASS__;
    protected function getDefaultModelClass(): string
    {
        return PaymentTransactionLocks::class;
    }
}
