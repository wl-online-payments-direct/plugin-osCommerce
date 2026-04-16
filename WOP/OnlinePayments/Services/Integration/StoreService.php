<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\classes\platform_config;
use common\helpers\Order;
use common\models\OrdersStatus;
use common\models\OrdersStatusGroups;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\OrderStatusMapping\Models\OrderStatusMapping;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Stores\Models\Store;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Configuration\ConfigurationManager;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
class StoreService implements \common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Stores\StoreService
{
    /**
     * @inheritDoc
     */
    public function getStoreDomain(): string
    {
        $platformConfig = new platform_config(StoreContext::getInstance()->getStoreId());
        return $platformConfig->getCatalogBaseUrl();
    }
    /**
     * @inheritDoc
     */
    public function getStores(): array
    {
        return [];
    }
    /**
     * @inheritDoc
     */
    public function getDefaultStore(): ?Store
    {
        return null;
    }
    /**
     * @inheritDoc
     */
    public function getStoreById(string $id): ?Store
    {
        return null;
    }
    /**
     * @inheritDoc
     */
    public function getDefaultOrderStatusMapping(): OrderStatusMapping
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::class);
        $refundedStatusId = $configurationManager->getConfigValue('refundedStatusId', null, \false);
        $statuses = OrdersStatus::find()->all();
        $capturedStatus = '';
        // Processing Orders/Payment successful
        $errorStatus = '';
        // New Orders/Processing
        $pendingStatus = '';
        // New Orders/Awaiting for payment
        $authorizedStatus = '';
        // New Orders/Redirected at payment gateway
        $cancelledStatus = '';
        // Cancelled Orders/Cancelled
        $refundedStatus = '';
        // Cancelled Orders/Refunded
        $partiallyRefundedStatus = '';
        // Processing Orders/Partially refunded
        foreach ($statuses as $status) {
            if ($status->orders_status_groups_id === OrdersStatusGroups::PROCESSING_GROUP && $status->orders_status_id === 100006) {
                $capturedStatus = $status->orders_status_id;
            }
            if ($status->orders_status_groups_id === OrdersStatusGroups::NEW_GROUP && $status->orders_status_id === 2) {
                $errorStatus = $status->orders_status_id;
            }
            if ($status->orders_status_groups_id === OrdersStatusGroups::NEW_GROUP && $status->orders_status_id === 100007) {
                $pendingStatus = $status->orders_status_id;
            }
            if ($status->orders_status_groups_id === OrdersStatusGroups::NEW_GROUP && $status->orders_status_id === 1) {
                $authorizedStatus = $status->orders_status_id;
            }
            if ($status->orders_status_groups_id === OrdersStatusGroups::CANCELLED_GROUP && $status->orders_status_id === 5) {
                $cancelledStatus = $status->orders_status_id;
            }
            if ($status->orders_status_groups_id === OrdersStatusGroups::CANCELLED_GROUP && $status->orders_status_id === $refundedStatusId) {
                $refundedStatus = $status->orders_status_id;
            }
            if ($status->orders_status_groups_id === OrdersStatusGroups::PROCESSING_GROUP && $status->orders_status_id === 100027) {
                $partiallyRefundedStatus = $status->orders_status_id;
            }
        }
        return new OrderStatusMapping($capturedStatus, $errorStatus, $pendingStatus, $authorizedStatus, $cancelledStatus, $refundedStatus, $partiallyRefundedStatus);
    }
    /**
     * @inheritDoc
     */
    public function getStoreOrderStatuses(): array
    {
        $languageId = \Yii::$app->settings->get('languages_id');
        $groups = \common\models\OrdersStatusGroups::find()->where(['language_id' => $languageId])->andWhere(['orders_status_type_id' => Order::getStatusTypeId()])->indexBy('orders_status_groups_id')->all();
        $groupIds = array_keys($groups);
        $statuses = OrdersStatus::find()->where(['language_id' => $languageId])->andWhere(['orders_status_groups_id' => $groupIds])->all();
        $result = [];
        $result[] = new StoreOrderStatus(0, 'None');
        foreach ($statuses as $status) {
            $groupName = $groups[$status->orders_status_groups_id]->orders_status_groups_name ?? '';
            $statusName = $groupName . '/' . $status->orders_status_name;
            $result[] = new StoreOrderStatus($status->orders_status_id, $statusName);
        }
        return $result;
    }
}
