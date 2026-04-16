<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\LogCleanup\Tasks;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\MonitoringLogRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\WebhookLogRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\Task;
/**
 * Class LogCleanupTask
 *
 * @package OnlinePayments\Core\Bootstrap\LogCleanup\Tasks
 */
class LogCleanupTask extends Task
{
    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $repository = $this->getMonitoringLogRepository();
        $this->deleteLogs($repository);
        $this->reportProgress(50);
        $repository = $this->getWebhookLogRepository();
        $this->deleteLogs($repository);
        $this->reportProgress(100);
    }
    /**
     * @param MonitoringLogRepositoryInterface | WebhookLogRepositoryInterface $repository
     *
     * @return void
     */
    protected function deleteLogs($repository): void
    {
        while ($repository->countExpired() > 0) {
            $repository->deleteExpired();
            $this->reportAlive();
        }
    }
    /**
     * @return MonitoringLogRepositoryInterface
     */
    protected function getMonitoringLogRepository(): MonitoringLogRepositoryInterface
    {
        return ServiceRegister::getService(MonitoringLogRepositoryInterface::class);
    }
    /**
     * @return WebhookLogRepositoryInterface
     */
    protected function getWebhookLogRepository(): WebhookLogRepositoryInterface
    {
        return ServiceRegister::getService(WebhookLogRepositoryInterface::class);
    }
}
