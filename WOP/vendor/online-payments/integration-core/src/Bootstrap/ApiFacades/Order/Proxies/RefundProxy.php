<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers\CreateRefundRequestTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers\CreateRefundResponseTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk\MerchantClientFactory;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\ContextLogProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Refund\RefundRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Refund\RefundResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Proxies\RefundProxyInterface;
/**
 * RefundProxy.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies
 */
class RefundProxy implements RefundProxyInterface
{
    private MerchantClientFactory $clientFactory;
    public function __construct(MerchantClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    public function create(RefundRequest $refundRequest): RefundResponse
    {
        ContextLogProvider::getInstance()->setPaymentNumber($refundRequest->getPaymentId()->getTransactionId());
        return CreateRefundResponseTransformer::transform($this->clientFactory->get()->payments()->refundPayment((string) $refundRequest->getPaymentId(), CreateRefundRequestTransformer::transform($refundRequest)));
    }
}
