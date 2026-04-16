<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethod;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethodCollection;
/**
 * Interface PaymentMethodConfigRepositoryInterface.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\Repositories
 */
interface PaymentMethodConfigRepositoryInterface
{
    /**
     * @return PaymentMethodCollection
     */
    public function getEnabled(): PaymentMethodCollection;
    public function getPaymentMethod(string $productId): ?PaymentMethod;
}
