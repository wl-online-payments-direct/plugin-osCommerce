<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Capture\CaptureResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\StatusCode;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CaptureResponse as SdkCaptureResponse;
/**
 * CreateCaptureResponseTransformer.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\Transformers
 */
class CreateCaptureResponseTransformer
{
    public static function transform(SdkCaptureResponse $response): CaptureResponse
    {
        return new CaptureResponse(PaymentId::parse($response->id), StatusCode::parse((int) $response->getStatusOutput()->getStatusCode()), $response->getStatus());
    }
}
