<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Logger\Logger;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Logging\CommunicatorLogger;
class ApiLogger implements CommunicatorLogger
{
    public function log($message): void
    {
        Logger::logInfo($message);
    }
    public function logException($message, Exception $exception): void
    {
        Logger::logError($message, 'Core', ['message' => $exception->getMessage(), 'type' => get_class($exception), 'trace' => $exception->getTraceAsString()]);
    }
}
