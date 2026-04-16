<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentAction;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\PaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\PaymentResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\Token;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\Payment;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentDetails;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\MethodAdditionalData\ThreeDSSettings\ThreeDSSettings;
/**
 * Interface PaymentsProxyInterface.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies
 */
interface PaymentsProxyInterface
{
    public function create(PaymentRequest $request, ThreeDSSettings $cardsSettings, PaymentSettings $paymentSettings, ?Token $token = null, ?PaymentAction $paymentAction = null): PaymentResponse;
    public function getPaymentDetails(PaymentId $paymentId): PaymentDetails;
    public function tryToGetPayment(PaymentId $paymentId): ?Payment;
    public function getPayment(PaymentId $paymentId): Payment;
    public function getRefunds(PaymentId $paymentId): array;
    public function getCaptures(PaymentId $paymentId): array;
}
