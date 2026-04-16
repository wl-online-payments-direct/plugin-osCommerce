<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Configuration\Configuration;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Logger\LogData;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Logger\Logger;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use Yii;
class LoggerService implements ShopLoggerAdapter
{
    /**
     * Log level names mapping
     *
     * @var array
     */
    private static array $logLevelName = [Logger::ERROR => 'ERROR', Logger::WARNING => 'WARNING', Logger::INFO => 'INFO', Logger::DEBUG => 'DEBUG'];
    /**
     * Log message to file
     *
     * @inheritDoc
     */
    public function logMessage(LogData $data): void
    {
        /** @var Configuration $configService */
        $configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        $minLogLevel = $configService->getMinLogLevel();
        $logLevel = $data->getLogLevel();
        if ($logLevel > $minLogLevel && !$configService->isDebugModeEnabled()) {
            return;
        }
        $brandCode = ModuleHelper::getModuleConfig()->getBrand();
        // Format message
        $message = $brandCode . ' LOG:' . ' | ' . 'Date: ' . date('d/m/Y', $data->getTimestamp()) . ' | ' . 'Time: ' . date('H:i:s', $data->getTimestamp()) . ' | ' . 'Log level: ' . self::$logLevelName[$logLevel] . ' | ' . 'Message: ' . $data->getMessage();
        // Add context if available
        $context = $data->getContext();
        if (!empty($context)) {
            $contextData = [];
            foreach ($context as $item) {
                $contextData[$item->getName()] = $item->getValue();
            }
            $message .= ' | Context: ' . json_encode($contextData);
        }
        // Log to Yii logger
        $this->writeLog($logLevel, $message);
    }
    /**
     * Write log message using Yii logger
     *
     * @param int $logLevel
     * @param string $message
     */
    protected function writeLog(int $logLevel, string $message): void
    {
        $category = ModuleHelper::getModuleConfig()->getModuleName();
        switch ($logLevel) {
            case Logger::ERROR:
                Yii::error($message, $category);
                break;
            case Logger::WARNING:
                Yii::warning($message, $category);
                break;
            case Logger::INFO:
                Yii::info($message, $category);
                break;
            case Logger::DEBUG:
                Yii::debug($message, $category);
                break;
        }
    }
}
