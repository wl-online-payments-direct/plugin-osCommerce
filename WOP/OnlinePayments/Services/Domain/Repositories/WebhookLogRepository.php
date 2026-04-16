<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Domain\Repositories;

use common\classes\TmpOrder;
use common\models\TmpOrders;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Monitoring\WebhookLogRepository as CoreWebhookLogRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\WebhookLog;
class WebhookLogRepository extends CoreWebhookLogRepository
{
    /**
     * @param WebhookLog $webhookLog
     *
     * @return string
     */
    public function getOrderUrl(WebhookLog $webhookLog): string
    {
        return \Yii::$app->urlManager->createAbsoluteUrl(['orders/process-order', 'orders_id' => $this->getOrderIdByTempOrderId($webhookLog->getOrderId())]);
    }
    public function getOrderUrlByOrderId(string $orderId): string
    {
        return \Yii::$app->urlManager->createAbsoluteUrl(['orders/process-order', 'orders_id' => $orderId]);
    }
    /**
     * @param string $merchantReference
     *
     * @return string
     */
    public function getOrderIdByTempOrderId(string $merchantReference): string
    {
        if (empty($merchantReference)) {
            return '';
        }
        $tmpOrderModel = TmpOrder::getARModel()->where(['orders_id' => $merchantReference])->one();
        return $tmpOrderModel ? $tmpOrderModel->child_id : '';
    }
    public function getOrderIdsByTempOrderIds(array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }
        $result = TmpOrders::find()->select(['orders_id', 'child_id'])->where(['orders_id' => $orderIds])->asArray()->all();
        $pairs = [];
        foreach ($result as $item) {
            $pairs[$item['orders_id']] = $item['child_id'];
        }
        return $pairs;
    }
}
