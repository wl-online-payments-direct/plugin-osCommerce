<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ApiFacades\Response\Response;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentTransaction;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\BackgroundProcesses\WaitPaymentOutcome;
/**
 * Class PaymentOutcomeResponse.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response
 */
class PaymentOutcomeResponse extends Response
{
    private WaitPaymentOutcome $paymentOutcome;
    public function __construct(WaitPaymentOutcome $paymentOutcome)
    {
        $this->paymentOutcome = $paymentOutcome;
    }
    public function toArray(): array
    {
        return ['isWaiting' => $this->isWaiting()];
    }
    public function isWaiting(): bool
    {
        return $this->paymentOutcome->isWaiting();
    }
    public function getPaymentTransaction(): PaymentTransaction
    {
        return $this->paymentOutcome->getPaymentTransaction();
    }
}
