<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Cancel\CancelResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\StatusCode;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CancelPaymentResponse;
/**
 * CreateCancelResponseTransformer.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers
 */
class CreateCancelResponseTransformer
{
    public static function transform(CancelPaymentResponse $response): CancelResponse
    {
        $payment = $response->getPayment();
        return new CancelResponse(PaymentId::parse($response->getPayment()->id), StatusCode::parse((int) $payment->getStatusOutput()->getStatusCode()), $payment->getStatus());
    }
}
