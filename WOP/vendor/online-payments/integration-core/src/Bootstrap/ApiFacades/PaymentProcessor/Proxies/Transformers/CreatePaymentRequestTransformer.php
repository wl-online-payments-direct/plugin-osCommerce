<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentAction;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\PaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\Token;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\MethodAdditionalData\ThreeDSSettings\ThreeDSSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\SurchargeSpecificInput;
/**
 * Class CreatePaymentRequestTransformer.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers
 */
class CreatePaymentRequestTransformer
{
    public static function transform(PaymentRequest $input, ThreeDSSettings $cardsSettings, PaymentSettings $paymentSettings, ?Token $token = null, ?PaymentAction $paymentAction = null): CreatePaymentRequest
    {
        $cart = $input->getCartProvider()->get();
        $request = new CreatePaymentRequest();
        if (null == $token) {
            $request->setHostedTokenizationId($input->getHostedTokenizationId());
        }
        $order = OrderTransformer::transform($cart);
        if ($paymentSettings->isApplySurcharge()) {
            $surchargeSpecificInput = new SurchargeSpecificInput();
            $surchargeSpecificInput->setMode('on-behalf-of');
            $order->setSurchargeSpecificInput($surchargeSpecificInput);
        }
        $request->setOrder($order);
        $request->setCardPaymentMethodSpecificInput(CardPaymentMethodSpecificInputTransformer::transform($cart, $input->getReturnUrl(), $cardsSettings, $paymentSettings, null, null, $token, $paymentAction));
        return $request;
    }
}
