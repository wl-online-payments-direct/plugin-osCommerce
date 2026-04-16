<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedCheckout\HostedCheckoutSessionRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\PaymentResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\Token;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\MethodAdditionalData\ThreeDSSettings\ThreeDSSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethodCollection;
/**
 * Interface HostedCheckoutProxyInterface.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies
 */
interface HostedCheckoutProxyInterface
{
    public function createSession(HostedCheckoutSessionRequest $request, ThreeDSSettings $cardsSettings, PaymentSettings $paymentSettings, PaymentMethodCollection $paymentMethodCollection, array $supportedPaymentMethods, ?Token $token = null): PaymentResponse;
}
