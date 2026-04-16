<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\MonitoringLogRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\WebhookLogRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Domain\Repositories\MonitoringLogRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Domain\Repositories\WebhookLogRepository;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;
class MonitoringController extends ModulesController
{
    /**
     * @return array
     *
     * @throws \Exception
     */
    public function actionGetMonitoringLogs(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $pageNumber = \Yii::$app->request->get('pageNumber');
        $pageSize = \Yii::$app->request->get('pageSize');
        $searchTerm = \Yii::$app->request->get('searchTerm') ?? '';
        $result = AdminAPI::get()->monitoringLogs($storeId)->getMonitoringLogs($pageNumber, $pageSize, $searchTerm);
        $arrayResult = $result->toArray();
        /** @var MonitoringLogRepository $repository */
        $repository = ServiceRegister::getService(MonitoringLogRepositoryInterface::class);
        $merchantReferences = array_column($arrayResult['monitoringLogs'], 'orderId');
        $orderIds = $repository->getOrderIdsByTempOrderIds($merchantReferences);
        foreach ($arrayResult['monitoringLogs'] as $key => $monitoringLog) {
            $orderId = $orderIds[$monitoringLog['orderId']] ?? '';
            $arrayResult['monitoringLogs'][$key]['orderId'] = (string) $orderId;
            $arrayResult['monitoringLogs'][$key]['orderLink'] = $orderId ? $repository->getOrderUrlByOrderId($orderId) : '';
        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $arrayResult;
    }
    /**
     * @return \yii\console\Response|Response
     *
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionDownloadMonitoringLogs()
    {
        $storeId = \Yii::$app->request->get('storeId');
        $result = AdminAPI::get()->monitoringLogs($storeId)->downloadMonitoringLogs();
        return \Yii::$app->response->sendContentAsFile(json_encode($result->toArray(), \JSON_PRETTY_PRINT), 'online-payments-monitoring-logs.json', ['mimeType' => 'application/json']);
    }
    public function actionGetWebhookLogs(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $pageNumber = \Yii::$app->request->get('pageNumber');
        $pageSize = \Yii::$app->request->get('pageSize');
        $searchTerm = \Yii::$app->request->get('searchTerm') ?? '';
        $result = AdminAPI::get()->monitoringLogs($storeId)->getWebhookLogs($pageNumber, $pageSize, $searchTerm);
        $arrayResult = $result->toArray();
        /** @var WebhookLogRepository $repository */
        $repository = ServiceRegister::getService(WebhookLogRepositoryInterface::class);
        $merchantReferences = array_column($arrayResult['webhookLogs'], 'orderId');
        $orderIds = $repository->getOrderIdsByTempOrderIds($merchantReferences);
        foreach ($arrayResult['webhookLogs'] as $key => $monitoringLog) {
            $orderId = $orderIds[$monitoringLog['orderId']] ?? '';
            $arrayResult['webhookLogs'][$key]['orderId'] = (string) $orderId;
            $arrayResult['webhookLogs'][$key]['orderLink'] = $orderId ? $repository->getOrderUrlByOrderId($orderId) : '';
        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $arrayResult;
    }
    public function actionDownloadWebhookLogs()
    {
        $storeId = \Yii::$app->request->get('storeId');
        $result = AdminAPI::get()->monitoringLogs($storeId)->downloadWebhookLogs();
        return \Yii::$app->response->sendContentAsFile(json_encode($result->toArray(), \JSON_PRETTY_PRINT), 'online-payments-webhook-logs.json', ['mimeType' => 'application/json']);
    }
}
