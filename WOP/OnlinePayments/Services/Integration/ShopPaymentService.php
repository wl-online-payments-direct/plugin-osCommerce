<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService as CoreShopPaymentService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethod;
class ShopPaymentService implements CoreShopPaymentService
{
    /**
     * @inheritDoc
     */
    public function savePaymentMethod(PaymentMethod $paymentMethod): void
    {
    }
    /**
     * @inheritDoc
     */
    public function enable(string $paymentProductId, bool $enabled): void
    {
    }
    /**
     * @inheritDoc
     */
    public function deletePaymentMethods(string $mode): void
    {
    }
}
