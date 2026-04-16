<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers\CreateCaptureRequestTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers\CreateCaptureResponseTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk\MerchantClientFactory;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Capture\CaptureRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Capture\CaptureResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\ContextLogProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Proxies\CaptureProxyInterface;
/**
 * CaptureProxy.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies
 */
class CaptureProxy implements CaptureProxyInterface
{
    private MerchantClientFactory $clientFactory;
    public function __construct(MerchantClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    public function create(CaptureRequest $captureRequest): CaptureResponse
    {
        ContextLogProvider::getInstance()->setPaymentNumber($captureRequest->getPaymentId()->getTransactionId());
        return CreateCaptureResponseTransformer::transform($this->clientFactory->get()->payments()->capturePayment((string) $captureRequest->getPaymentId(), CreateCaptureRequestTransformer::transform($captureRequest)));
    }
}
