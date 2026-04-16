<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\DefaultConnection;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CommunicatorConfiguration;
class DbLogConnection extends DefaultConnection
{
    private CommunicatorLoggerHelper $communicatorLoggerHelper;
    public function __construct(CommunicatorLoggerHelper $communicatorLoggerHelper, ?CommunicatorConfiguration $communicatorConfiguration = null)
    {
        parent::__construct($communicatorConfiguration);
        $this->communicatorLoggerHelper = $communicatorLoggerHelper;
    }
    protected function getCommunicatorLoggerHelper(): CommunicatorLoggerHelper
    {
        return $this->communicatorLoggerHelper;
    }
}
