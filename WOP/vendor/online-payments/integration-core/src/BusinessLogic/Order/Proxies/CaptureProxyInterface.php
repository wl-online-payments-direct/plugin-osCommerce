<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Capture\CaptureRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Capture\CaptureResponse;
/**
 * Interface CaptureProxyInterface.
 *
 * @package OnlinePayments\Core\BusinessLogic\Order\Proxies
 */
interface CaptureProxyInterface
{
    public function create(CaptureRequest $captureRequest): CaptureResponse;
}
