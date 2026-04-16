<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Models\PaymentTransactions;
/**
 * Class PaymentTransactionsRepository.
 *
 * @package OnlinePayments\Repositories
 */
class PaymentTransactionsRepository extends BaseRepositoryWithConditionalDelete
{
    public const THIS_CLASS_NAME = __CLASS__;
    protected function getDefaultModelClass(): string
    {
        return PaymentTransactions::class;
    }
}
