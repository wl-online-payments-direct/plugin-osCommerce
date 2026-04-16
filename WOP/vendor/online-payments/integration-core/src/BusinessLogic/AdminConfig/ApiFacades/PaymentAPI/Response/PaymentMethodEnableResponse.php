<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Response;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ApiFacades\Response\Response;
/**
 * Class PaymentMethodEnableResponse
 *
 * @package OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Response
 */
class PaymentMethodEnableResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [];
    }
}
