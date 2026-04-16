<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\GeneralSettingsAPI\Response;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ApiFacades\Response\Response;
/**
 * Class DisconnectResponse
 *
 * @package OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\GeneralSettingsAPI\Response
 */
class DisconnectResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [];
    }
}
