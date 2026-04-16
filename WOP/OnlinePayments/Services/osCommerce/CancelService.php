<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\osCommerce;

use common\classes\Order;
use common\models\OrdersPayment;
use common\services\OrderManager;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\OrderAPI\OrderAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Cancel\CancelRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Amount;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
class CancelService extends MerchantActionService
{
    /**
     * @param $transactionId
     *
     * @return bool
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    public function canCancel($transactionId): bool
    {
        $transaction = $this->getTransactionEntity($transactionId);
        if (!$transaction) {
            return \false;
        }
        $orderDetailsResponse = OrderAPI::get()->orders($transaction->getStoreId())->getDetails($transaction->getMerchantReference());
        if (!$orderDetailsResponse->isSuccessful()) {
            return \false;
        }
        $orderDetails = $orderDetailsResponse->getOrderDetails();
        return $orderDetails->getCancel()->isPossible();
    }
    public function cancel($transactionId, $requestedAmount): bool
    {
        $code = ModuleHelper::getModuleConfig()->getModuleName();
        $transaction = $this->getTransactionEntity($transactionId);
        $orderDetailsResponse = OrderAPI::get()->orders($transaction->getStoreId())->getDetails($transaction->getMerchantReference());
        if (!$orderDetailsResponse->isSuccessful()) {
            return \false;
        }
        $orderDetails = $orderDetailsResponse->getOrderDetails();
        // Get the maximum cancellable amount (remaining uncaptured amount)
        $maxCancellableAmount = $orderDetails->getCancel()->getAvailable();
        if ($maxCancellableAmount->getValue() <= 0) {
            return \false;
        }
        // Determine the actual void amount
        if ($requestedAmount > 0) {
            // Merchant specified an amount - validate it doesn't exceed cancellable amount
            $currency = $maxCancellableAmount->getCurrency();
            $voidAmount = Amount::fromFloat($requestedAmount, $currency);
            // Validate the requested amount doesn't exceed what can be cancelled
            if ($voidAmount->getValue() > $maxCancellableAmount->getValue()) {
                \Yii::error('Void amount (' . $requestedAmount . ') exceeds cancellable amount (' . $maxCancellableAmount->getPriceInCurrencyUnits() . ')', $this->code);
                return \false;
            }
        } else {
            // No amount specified - void the full cancellable amount
            $voidAmount = $maxCancellableAmount;
        }
        // Get the order ID from the payment record
        $paymentRecord = OrdersPayment::find()->where(['orders_payment_transaction_id' => $transactionId])->andWhere(['orders_payment_module' => $code])->one();
        if (!$paymentRecord) {
            return \false;
        }
        $orderId = $paymentRecord->orders_payment_order_id;
        $order = OrderManager::loadManager()->getOrderInstanceWithId(Order::class, $orderId);
        // Get all transactions to find the main one
        $transactions = OrdersPayment::find()->where(['orders_payment_order_id' => $orderId])->andWhere(['orders_payment_module' => $code])->orderBy(['orders_payment_date_create' => \SORT_ASC])->all();
        if (empty($transactions)) {
            return \false;
        }
        $mainTransaction = $transactions[0];
        // Create cancel request with the determined void amount
        $cancelRequest = new CancelRequest(PaymentId::parse($transactionId), $voidAmount, $transaction->getMerchantReference());
        $result = OrderAPI::get()->cancel($transaction->getStoreId())->handle($cancelRequest);
        return $this->createNewPaymentTransactionRecord($mainTransaction, $orderId, $voidAmount->getPriceInCurrencyUnits(), $order, $result, $transaction);
    }
}
