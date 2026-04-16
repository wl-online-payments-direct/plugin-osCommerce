<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\osCommerce;

use common\classes\extended\OrderAbstract;
use common\helpers\Date;
use common\helpers\OrderPayment as OrderPaymentHelper;
use common\helpers\Translation;
use common\models\OrdersPayment;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\OrderAPI\OrderAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\PaymentTransactionEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ApiFacades\Response\Response;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\ApiFacades\OrdersAPI\Response\OrderDetailsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\Operators;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\RepositoryRegistry;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\CommentaryTranslationHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\PaymentTransactionsRepository;
use function common\modules\orderPayment\WOP\GuzzleHttp\Psr7\str;
class MerchantActionService
{
    public function __construct()
    {
        Translation::init('payment');
    }
    public function createNewPaymentTransactionRecord($mainTransaction, $orderId, float $amount, OrderAbstract $order, Response $result, PaymentTransactionEntity $paymentTransactionEntity)
    {
        $transaction = $this->getNewPaymentTransactionRecord($mainTransaction, $orderId, $amount, $order, $result, $paymentTransactionEntity);
        if (!$transaction->save()) {
            return \false;
        }
        if ($order) {
            $order->update_piad_information(\true);
        }
        return \true;
    }
    public function getNewPaymentTransactionRecord($mainTransaction, $orderId, float $amount, OrderAbstract $order, Response $result, PaymentTransactionEntity $paymentTransactionEntity): OrdersPayment
    {
        $response = $result->getResponse();
        $orderDetailsResponse = OrderAPI::get()->orders($paymentTransactionEntity->getStoreId())->getDetails($paymentTransactionEntity->getMerchantReference());
        $orderDetails = $orderDetailsResponse->getOrderDetails();
        $newTransaction = new OrdersPayment();
        $existingTransaction = OrdersPayment::find()->where(['orders_payment_transaction_id' => (string) $response->getId()])->one();
        if ($existingTransaction) {
            $newTransaction = $existingTransaction;
        }
        $payments = $orderDetails->getPayments();
        $details = array_pop($payments);
        $newTransaction->orders_payment_id_parent = $mainTransaction->orders_payment_id;
        $newTransaction->orders_payment_order_id = $orderId;
        $newTransaction->orders_payment_module = ModuleHelper::getModuleConfig()->getModuleName();
        $newTransaction->orders_payment_module_name = $mainTransaction->orders_payment_module_name;
        $newTransaction->orders_payment_is_credit = 0;
        $newTransaction->orders_payment_amount = $amount;
        $newTransaction->orders_payment_currency = $mainTransaction->orders_payment_currency;
        $newTransaction->orders_payment_currency_rate = $mainTransaction->orders_payment_currency_rate;
        $newTransaction->orders_payment_snapshot = json_encode(OrderPaymentHelper::getOrderPaymentSnapshot($order));
        $newTransaction->orders_payment_transaction_id = (string) $response->getId();
        $newTransaction->orders_payment_transaction_status = $response->getStatus();
        $newTransaction->orders_payment_transaction_commentary = $this->getTransactionCommentary($details->getFraudResult() ?? '', $details->getLiability() ?? '', $details->getExemptionType() ?? '');
        $newTransaction->orders_payment_transaction_full = json_encode($result->toArray());
        $newTransaction->orders_payment_date_create = date(Date::DATABASE_DATETIME_FORMAT);
        $newTransaction->orders_payment_admin_create = $_SESSION['login_id'] ?? 0;
        $newTransaction->orders_payment_status = ModuleHelper::getPaymentTransactionStatus($response->getStatusCode());
        return $newTransaction;
    }
    public function getTransactionCommentary(string $fraudResult, string $liability, string $exemptionType): string
    {
        return CommentaryTranslationHelper::createCommentary($fraudResult, $liability, $exemptionType);
    }
    /**
     * @param string $transactionId
     *
     * @return PaymentTransactionEntity|null
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    protected function getTransactionEntity(string $transactionId): ?PaymentTransactionEntity
    {
        /** @var PaymentTransactionsRepository $paymentTransactionsRepository */
        $paymentTransactionsRepository = RepositoryRegistry::getRepository(PaymentTransactionEntity::getClassName());
        $queryFilter = new QueryFilter();
        $queryFilter->where('transactionId', Operators::EQUALS, PaymentId::parse($transactionId)->getTransactionId());
        /** @var PaymentTransactionEntity $transaction */
        $transaction = $paymentTransactionsRepository->selectOne($queryFilter);
        return $transaction;
    }
}
