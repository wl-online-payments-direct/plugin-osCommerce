<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\osCommerce;

use common\classes\Order;
use common\models\OrdersPayment;
use common\services\OrderManager;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\OrderAPI\OrderAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Capture\CaptureRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Amount;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Currency;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
class CaptureService extends MerchantActionService
{
    /**
     * @param string $transaction_id
     *
     * @return bool
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    public function canCapture(string $transaction_id): bool
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
        return $orderDetails->getCapture()->isPossible();
    }
    /**
     * @param string $transaction_id
     * @param float $amount
     *
     * @return bool
     *
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    public function capture(string $transaction_id, float $amount): bool
    {
        $code = ModuleHelper::getModuleConfig()->getModuleName();
        $transaction = $this->getTransactionEntity($transaction_id);
        $orderDetailsResponse = OrderAPI::get()->orders($transaction->getStoreId())->getDetails($transaction->getMerchantReference());
        if (!$orderDetailsResponse->isSuccessful()) {
            return \false;
        }
        // Get the order ID from the payment record
        $paymentRecord = OrdersPayment::find()->where(['orders_payment_transaction_id' => $transaction_id])->andWhere(['orders_payment_module' => $code])->one();
        if (!$paymentRecord) {
            return \false;
        }
        $orderId = $paymentRecord->orders_payment_order_id;
        $order = OrderManager::loadManager()->getOrderInstanceWithId(Order::class, $orderId);
        // Get capture information
        $transactions = OrdersPayment::find()->where(['orders_payment_order_id' => $orderId])->andWhere(['orders_payment_module' => $code])->orderBy(['orders_payment_date_create' => \SORT_ASC])->all();
        if (empty($transactions)) {
            return \false;
        }
        $mainTransaction = $transactions[0];
        $authorizedAmount = $orderDetailsResponse->getOrderDetails()->getAmount();
        $currency = Currency::fromIsoCode($mainTransaction->orders_payment_currency);
        $capturedAmount = Amount::fromFloat($amount, $currency);
        $remainingAmount = $authorizedAmount->minus($capturedAmount);
        if ($amount <= 0 || $remainingAmount->getPriceInCurrencyUnits() < 0) {
            return \false;
        }
        $result = OrderAPI::get()->capture($transaction->getStoreId())->handle(new CaptureRequest(PaymentId::parse($transaction_id), $capturedAmount, $transaction->getMerchantReference()));
        return $this->createNewPaymentTransactionRecord($mainTransaction, $orderId, $amount, $order, $result, $transaction);
    }
}
