<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Amount;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Currency;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\StatusCode;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Refund\RefundResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\RefundResponse as SdkRefundResponse;
/**
 * CreateRefundResponseTransformer.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers
 */
class CreateRefundResponseTransformer
{
    public static function transform(SdkRefundResponse $response): RefundResponse
    {
        return new RefundResponse(PaymentId::parse($response->id), StatusCode::parse((int) $response->getStatusOutput()->getStatusCode()), $response->getStatus(), Amount::fromInt($response->getRefundOutput()->getAmountOfMoney()->getAmount(), Currency::fromIsoCode($response->getRefundOutput()->getAmountOfMoney()->getCurrencyCode())));
    }
}
