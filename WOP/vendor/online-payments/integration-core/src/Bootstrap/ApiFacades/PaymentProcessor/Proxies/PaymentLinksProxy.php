<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers\CreatePaymentLinkRequestTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers\CreatePaymentLinkResponseTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk\MerchantClientFactory;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PayByLinkSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\ContextLogProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentLinks\PaymentLinkRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentLinks\PaymentLinkResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\MethodAdditionalData\ThreeDSSettings\ThreeDSSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethodCollection;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\PaymentLinksProxyInterface;
/**
 * Class PaymentLinksProxy.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies
 */
class PaymentLinksProxy implements PaymentLinksProxyInterface
{
    private MerchantClientFactory $clientFactory;
    public function __construct(MerchantClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    public function create(PaymentLinkRequest $request, ThreeDSSettings $cardsSettings, PaymentSettings $paymentSettings, PayByLinkSettings $payByLinkSettings, PaymentMethodCollection $paymentMethodCollection, array $supportedPaymentMethods): PaymentLinkResponse
    {
        ContextLogProvider::getInstance()->setCurrentOrder($request->getCartProvider()->get()->getMerchantReference());
        return CreatePaymentLinkResponseTransformer::transform($this->clientFactory->get()->paymentLinks()->createPaymentLink(CreatePaymentLinkRequestTransformer::transform($request, $cardsSettings, $paymentSettings, $payByLinkSettings, $paymentMethodCollection, $supportedPaymentMethods)));
    }
    public function getById(string $paymentLinkId, string $merchantReference): PaymentLinkResponse
    {
        ContextLogProvider::getInstance()->setCurrentOrder($merchantReference);
        return CreatePaymentLinkResponseTransformer::transform($this->clientFactory->get()->paymentLinks()->getPaymentLinkById($paymentLinkId));
    }
}
