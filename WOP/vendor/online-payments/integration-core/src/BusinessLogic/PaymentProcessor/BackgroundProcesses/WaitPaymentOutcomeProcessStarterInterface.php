<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\BackgroundProcesses;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
/**
 * Interface WaitPaymentOutcomeProcessStarterInterface.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\BackgroundProcesses
 */
interface WaitPaymentOutcomeProcessStarterInterface
{
    public function startInBackground(?PaymentId $paymentId, ?string $returnHmac = null, ?string $merchantReference = null): void;
}
