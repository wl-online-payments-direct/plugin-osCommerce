<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers\CalculateSurchargeRequestTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers\CalculateSurchargeResponseTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk\MerchantClientFactory;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\SurchargeRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\SurchargeResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\SurchargeProxyInterface;
/**
 * Class SurchargeProxy
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies
 */
class SurchargeProxy implements SurchargeProxyInterface
{
    private MerchantClientFactory $clientFactory;
    public function __construct(MerchantClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    /**
     * @inheritDoc
     */
    public function calculateSurcharge(SurchargeRequest $request): ?SurchargeResponse
    {
        return CalculateSurchargeResponseTransformer::transform($this->clientFactory->get()->services()->surchargeCalculation(CalculateSurchargeRequestTransformer::transform($request)));
    }
}
