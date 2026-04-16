<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Response;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ApiFacades\Response\Response;
/**
 * Class PaymentMethodSaveResponse
 *
 * @package OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Response
 */
class PaymentMethodSaveResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [];
    }
}
