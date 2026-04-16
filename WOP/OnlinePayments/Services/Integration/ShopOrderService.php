<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\classes\extended\OrderAbstract;
use common\classes\modules\ModuleBuilder;
use common\classes\modules\ModulePayment;
use common\classes\TmpOrder;
use common\helpers\Date;
use common\helpers\Order;
use common\helpers\OrderPayment;
use common\models\OrdersPayment;
use common\services\OrderManager;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\OrderAPI\OrderAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\ShopOrderService as ShopOrderServiceInterfacew;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentDetails;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentTransaction;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\Repositories\PaymentTransactionRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\ApiFacades\OrdersAPI\Response\OrderDetailsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\PaymentsProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Services\osCommerce\MerchantActionService;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
/**
 * Class ShopOrderService.
 *
 * @package OnlinePayments\Services\Integration
 */
class ShopOrderService implements ShopOrderServiceInterfacew
{
    public function createShopOrder(PaymentTransaction $paymentTransaction, PaymentDetails $paymentDetails, string $newState): void
    {
        $moduleBuilder = new ModuleBuilder(OrderManager::loadManager());
        /** @var ModulePayment $module */
        $module = $moduleBuilder(['class' => "\\common\\modules\\orderPayment\\" . ModuleHelper::getModuleConfig()->getModuleName()]);
        $tmpOrderModel = TmpOrder::getARModel()->where(['orders_id' => $paymentTransaction->getMerchantReference()])->one();
        if (!$tmpOrderModel) {
            return;
        }
        if ($tmpOrderModel->child_id > 0) {
            $this->updateStatus($paymentTransaction, $paymentDetails, $newState);
            $this->createTransaction($paymentTransaction, $paymentDetails);
            return;
        }
        /** @var TmpOrder $tmpOrder */
        $tmpOrder = OrderManager::loadManager()->getParentToInstanceWithId(TmpOrder::class, $paymentTransaction->getMerchantReference());
        $tmpOrder->info['order_status'] = (int) $newState;
        $orderDetails = OrderAPI::get()->orders($tmpOrder->info['platform_id'])->getDetails($paymentTransaction->getMerchantReference())->getOrderDetails();
        $paymentTitle = $module->getTitle();
        $paymentTitleParts = [];
        foreach ($orderDetails->getPayments() as $payment) {
            $paymentTitleParts[] = $module->getTitle() . ' - ' . $payment->getPaymentMethodName();
        }
        if (!empty($paymentTitleParts)) {
            $paymentTitle = implode("\n", $paymentTitleParts);
        }
        $tmpOrder->info['payment_method'] = $paymentTitle;
        $tmpOrder->info['payment_info'] = $paymentTitle;
        $tmpOrder->save_order((int) $paymentTransaction->getMerchantReference());
        $orderId = $tmpOrder->createOrder();
        $order = $module->manager->getOrderInstanceWithId(\common\classes\Order::class, $orderId);
        $this->createOrUpdateOrderPayments($module, $paymentTransaction, $orderId, $paymentDetails, $order);
        $order->update_piad_information(\true);
        $order->save_totals();
        $module->finalizeCheckout((int) $orderId, $paymentTransaction->getPaymentId());
    }
    public function updateStatus(PaymentTransaction $paymentTransaction, PaymentDetails $paymentDetails, string $newState): void
    {
        $tmpOrderModel = TmpOrder::getARModel()->where(['orders_id' => $paymentTransaction->getMerchantReference()])->one();
        if (!$tmpOrderModel) {
            return;
        }
        if ($tmpOrderModel->child_id > 0) {
            Order::setStatus($tmpOrderModel->child_id, $newState);
        }
    }
    public function cancelShopOrder(PaymentTransaction $paymentTransaction, PaymentDetails $paymentDetails, string $newState): void
    {
        $this->updateStatus($paymentTransaction, $paymentDetails, $newState);
        $this->createTransaction($paymentTransaction, $paymentDetails);
    }
    public function refundShopOrder(PaymentTransaction $paymentTransaction, PaymentDetails $paymentDetails, string $newState): void
    {
        $this->updateStatus($paymentTransaction, $paymentDetails, $newState);
        $this->createTransaction($paymentTransaction, $paymentDetails);
    }
    public function updateTransactions(OrderDetailsResponse $orderDetailsResponse)
    {
        /** @var PaymentTransactionRepositoryInterface $repository */
        $repository = ServiceRegister::getService(PaymentTransactionRepositoryInterface::class);
        /** @var PaymentsProxyInterface $proxy */
        $proxy = ServiceRegister::getService(PaymentsProxyInterface::class);
        $payments = $orderDetailsResponse->getOrderDetails()->getPayments();
        $payment = reset($payments);
        $paymentTransaction = $repository->get($payment->getId());
        $paymentDetails = $proxy->getPaymentDetails($payment->getId());
        $this->createTransaction($paymentTransaction, $paymentDetails);
    }
    /**
     * @param ModulePayment $module
     * @param PaymentTransaction $paymentTransaction
     * @param int $orderId
     * @param PaymentDetails $paymentDetails
     * @param OrderAbstract $order
     *
     * @return void
     *
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    private function createOrUpdateOrderPayments(ModulePayment $module, PaymentTransaction $paymentTransaction, int $orderId, PaymentDetails $paymentDetails, OrderAbstract $order): void
    {
        $paymentTitle = $module->getTitle();
        $service = new MerchantActionService();
        $amountCurrency = $paymentDetails->getAmount()->getCurrency()->getIsoCode();
        $currencies = \Yii::$container->get('currencies');
        $currencyRate = 1;
        if (array_key_exists($amountCurrency, $currencies->currencies)) {
            $currencyRate = $currencies->currencies[$amountCurrency]['value'];
        }
        $transactionIds = [];
        foreach ($paymentDetails->getOperations() as $operation) {
            $transactionIds[] = (string) $operation->getId();
        }
        $orderDetails = OrderAPI::get()->orders($order->info['platform_id'])->getDetails($paymentTransaction->getMerchantReference())->getOrderDetails();
        $orderPayments = OrdersPayment::find()->where(['orders_payment_transaction_id' => $transactionIds])->all();
        $payments = $orderDetails->getPayments();
        $firstPayment = reset($payments);
        foreach ($paymentDetails->getOperations() as $operation) {
            $paymentId = $operation->getId();
            $orderPayment = null;
            foreach ($orderPayments as $item) {
                if ($item->orders_payment_transaction_id === (string) $paymentId) {
                    $orderPayment = $item;
                }
            }
            $paymentMethodName = '';
            foreach ($payments as $item) {
                if ((string) $item->getId() === (string) $paymentId) {
                    $paymentMethodName = $item->getPaymentMethodName();
                }
            }
            if (empty($paymentMethodName)) {
                $paymentMethodName = $firstPayment->getPaymentMethodName();
            }
            if (!$orderPayment) {
                $orderPayment = new OrdersPayment();
            }
            $orderPayment->orders_payment_module = $module->code;
            $orderPayment->orders_payment_module_name = $paymentTitle . ' - ' . $paymentMethodName;
            $orderPayment->orders_payment_transaction_id = (string) $paymentId;
            $orderPayment->orders_payment_id_parent = 0;
            $orderPayment->orders_payment_order_id = $orderId;
            $orderPayment->orders_payment_is_credit = $operation->getStatus() === 'REFUNDED' ? 1 : 0;
            $orderPayment->orders_payment_status = ModuleHelper::getPaymentTransactionStatus($operation->getStatusCode());
            $orderPayment->orders_payment_amount = $operation->getAmount()->getPriceInCurrencyUnits();
            $orderPayment->orders_payment_currency = $amountCurrency;
            $orderPayment->orders_payment_currency_rate = $currencyRate;
            $orderPayment->orders_payment_snapshot = json_encode(OrderPayment::getOrderPaymentSnapshot($order));
            $orderPayment->orders_payment_transaction_status = $operation->getStatus();
            $orderPayment->orders_payment_transaction_commentary = $service->getTransactionCommentary($paymentDetails->getPaymentSpecificOutput()->getFraudResult() ?? '', $paymentDetails->getPaymentSpecificOutput()->getThreeDsLiability() ?? '', $paymentDetails->getPaymentSpecificOutput()->getThreeDsExemptionType() ?? '');
            $orderPayment->orders_payment_date_create = date(Date::DATABASE_DATETIME_FORMAT);
            $orderPayment->save(\false);
        }
    }
    /**
     * @param PaymentTransaction $paymentTransaction
     * @param PaymentDetails $paymentDetails
     *
     * @return void
     *
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function createTransaction(PaymentTransaction $paymentTransaction, PaymentDetails $paymentDetails): void
    {
        $tmpOrderModel = TmpOrder::getARModel()->where(['orders_id' => $paymentTransaction->getMerchantReference()])->one();
        if (!$tmpOrderModel) {
            return;
        }
        $moduleBuilder = new ModuleBuilder(OrderManager::loadManager());
        /** @var ModulePayment $module */
        $module = $moduleBuilder(['class' => "\\common\\modules\\orderPayment\\" . ModuleHelper::getModuleConfig()->getModuleName()]);
        $orderId = $tmpOrderModel->child_id;
        $order = $module->manager->getOrderInstanceWithId(\common\classes\Order::class, $orderId);
        $this->createOrUpdateOrderPayments($module, $paymentTransaction, $orderId, $paymentDetails, $order);
        $order->updatePaidTotals(\true);
    }
}
