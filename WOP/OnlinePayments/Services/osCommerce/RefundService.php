<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\osCommerce;

use common\classes\extended\OrderAbstract;
use common\classes\Order;
use common\models\OrdersPayment;
use common\services\OrderManager;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\OrderAPI\OrderAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\PaymentTransactionEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ApiFacades\Response\Response;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Refund\RefundRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Amount;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Currency;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\ApiFacades\OrdersAPI\Response\OrderDetailsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
class RefundService extends MerchantActionService
{
    /**
     * @param string $transaction_id
     *
     * @return bool
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    public function canRefund(string $transaction_id): bool
    {
        $transaction = $this->getTransactionEntity($transaction_id);
        if (!$transaction) {
            return \false;
        }
        $orderDetailsResponse = OrderAPI::get()->orders($transaction->getStoreId())->getDetails($transaction->getMerchantReference());
        if (!$orderDetailsResponse->isSuccessful()) {
            return \false;
        }
        $orderDetails = $orderDetailsResponse->getOrderDetails();
        return $orderDetails->getRefund()->isPossible();
    }
    /**
     * @param string $transaction_id
     * @param float $amount
     *
     * @return bool
     *
     * @throws InvalidCurrencyCode
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    public function refund(string $transaction_id, float $amount): bool
    {
        $code = ModuleHelper::getModuleConfig()->getModuleName();
        $transaction = $this->getTransactionEntity($transaction_id);
        // Get the order ID from the payment record
        $paymentRecord = OrdersPayment::find()->where(['orders_payment_transaction_id' => $transaction_id])->andWhere(['orders_payment_module' => $code])->one();
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
        // Get the currency and create the refund amount
        $currency = Currency::fromIsoCode($mainTransaction->orders_payment_currency);
        $refundAmount = Amount::fromFloat($amount, $currency);
        // Validate amount
        if ($amount <= 0) {
            return \false;
        }
        // Create refund request
        $refundRequest = new RefundRequest(PaymentId::parse($transaction_id), $refundAmount, $transaction->getMerchantReference());
        $result = OrderAPI::get()->refund($transaction->getStoreId())->handle($refundRequest);
        return $this->createNewPaymentTransactionRecord($mainTransaction, $orderId, $result->getResponse()->getAmount()->getPriceInCurrencyUnits(), $order, $result, $transaction);
    }
    /**
     * @param $mainTransaction
     * @param $orderId
     * @param float $amount
     * @param OrderAbstract $order
     * @param Response $result
     * @param PaymentTransactionEntity $paymentTransactionEntity
     *
     * @return bool
     */
    public function createNewPaymentTransactionRecord($mainTransaction, $orderId, float $amount, OrderAbstract $order, Response $result, PaymentTransactionEntity $paymentTransactionEntity): bool
    {
        $refundTransaction = $this->getNewPaymentTransactionRecord($mainTransaction, $orderId, $amount, $order, $result, $paymentTransactionEntity);
        $refundTransaction->orders_payment_is_credit = 1;
        if (!$refundTransaction->save()) {
            return \false;
        }
        if ($order) {
            $order->update_piad_information(\true);
        }
        return \true;
    }
}
