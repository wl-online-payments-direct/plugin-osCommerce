<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ApiFacades\Response\Response;
/**
 * Class UpdateStatusResponse.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response
 */
class UpdateStatusResponse extends Response
{
    public function toArray(): array
    {
        return [];
    }
}
