<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PayByLinkSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentLinks\PaymentLinkRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentLinks\PaymentLinkResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\MethodAdditionalData\ThreeDSSettings\ThreeDSSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethodCollection;
/**
 * Interface PaymentLinksProxyInterface.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies
 */
interface PaymentLinksProxyInterface
{
    public function create(PaymentLinkRequest $request, ThreeDSSettings $cardsSettings, PaymentSettings $paymentSettings, PayByLinkSettings $payByLinkSettings, PaymentMethodCollection $paymentMethodCollection, array $supportedPaymentMethods): PaymentLinkResponse;
    public function getById(string $paymentLinkId, string $merchantReference): PaymentLinkResponse;
}
