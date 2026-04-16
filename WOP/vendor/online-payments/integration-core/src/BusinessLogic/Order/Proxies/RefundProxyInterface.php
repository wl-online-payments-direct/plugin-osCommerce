<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Refund\RefundRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Refund\RefundResponse;
/**
 * Interface RefundProxyInterface.
 *
 * @package OnlinePayments\Core\BusinessLogic\Order\Proxies
 */
interface RefundProxyInterface
{
    public function create(RefundRequest $refundRequest): RefundResponse;
}
