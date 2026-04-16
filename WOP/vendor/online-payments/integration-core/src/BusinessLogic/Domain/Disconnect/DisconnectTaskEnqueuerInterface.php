<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Disconnect;

/**
 * Interface DisconnectTaskEnqueuerInterface
 *
 * @package OnlinePayments\Core\BusinessLogic\Domain\Disconnect
 */
interface DisconnectTaskEnqueuerInterface
{
    /**
     * @param string $mode
     *
     * @return void
     */
    public function enqueueDisconnectTask(string $mode): void;
}
