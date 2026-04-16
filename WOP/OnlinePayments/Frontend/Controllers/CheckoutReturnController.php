<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Frontend\Controllers;

use common\classes\modules\ModuleBuilder;
use common\classes\modules\ModulePayment;
use common\classes\platform;
use common\classes\TmpOrder;
use common\helpers\Translation;
use common\services\OrderManager;
use frontend\controllers\Sceleton;
use common\modules\orderPayment\WOP\OnlinePayments\Common\Controllers\ControllerViewPathResolver;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\CheckoutAPI\CheckoutAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use yii\web\JqueryAsset;
use yii\web\Response;
/**
 * Class CheckoutReturnController.
 *
 * @package OnlinePayments\Frontend\Controllers
 */
class CheckoutReturnController extends Sceleton
{
    use ControllerViewPathResolver;
    public function actionIndex()
    {
        if (!$this->validateAccess()) {
            return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['checkout/index']));
        }
        $paymentId = \Yii::$app->request->get('paymentId', \Yii::$app->request->get('hostedCheckoutId'));
        CheckoutAPI::get()->payment((string) platform::currentId())->startWaitingForOutcomeInBackground(PaymentId::parse($paymentId), \Yii::$app->request->get('RETURNMAC'), \Yii::$app->request->get('merchantReference'));
        $ajaxUrl = $this->getCheckOutcomeUrl($paymentId);
        \Yii::$app->getView()->registerJsFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Frontend/assets/js/payment-status-checker.js'), ['appendTimestamp' => \true, 'depends' => [JqueryAsset::class]]);
        Translation::init('payment');
        return self::render('index', ['title' => ModuleHelper::getConstantValue('TEXT_WAITING_TITLE'), 'pendingTransMsg' => ModuleHelper::getConstantValue('TEXT_PENDING_TRANS_MSG'), 'pendingTransDetails' => ModuleHelper::getConstantValue('TEXT_PENDING_TRANS_DETAILS'), 'pendingTransInstructions' => ModuleHelper::getConstantValue('TEXT_PENDING_TRANS_INSTRUCTIONS'), 'paymentIdLabel' => ModuleHelper::getConstantValue('TEXT_PAYMENT_ID_LABEL'), 'hostedCheckoutIdLabel' => ModuleHelper::getConstantValue('TEXT_HOSTED_CHECKOUT_ID_LABEL'), 'ajax_url' => $ajaxUrl, 'loaderImageUrl' => ModuleHelper::getAdminAssetUrl('/OnlinePayments/Frontend/assets/images/loader.svg'), 'contactUsLink' => \Yii::$app->urlManager->createUrl([FILENAME_CONTACT_US]), 'hostedCheckoutId' => \Yii::$app->request->get('hostedCheckoutId'), 'paymentId' => \Yii::$app->request->get('paymentId')]);
    }
    public function actionPaymentOutcome(): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$this->validateAccess()) {
            return ['success' => \true, 'redirect_url' => \Yii::$app->urlManager->createUrl(['checkout/index'])];
        }
        $paymentId = \Yii::$app->request->get('paymentId', \Yii::$app->request->get('hostedCheckoutId'));
        $response = CheckoutAPI::get()->payment((string) platform::currentId())->getPaymentOutcome(PaymentId::parse($paymentId), \Yii::$app->request->get('RETURNMAC'), \Yii::$app->request->get('merchantReference'));
        if (!$response->isSuccessful() || $response->isWaiting()) {
            return ['success' => $response->isSuccessful()];
        }
        if ($response->getPaymentTransaction()->getStatusCode()->isCanceledOrRejected()) {
            return ['success' => \true, 'redirect_url' => \Yii::$app->urlManager->createUrl(['checkout/index', 'payment_error' => ModuleHelper::getModuleConfig()->getModuleName()])];
        }
        $tmpOrder = TmpOrder::getARModel()->where(['orders_id' => $response->getPaymentTransaction()->getMerchantReference()])->one();
        if ($tmpOrder->child_id > 0) {
            $moduleBuilder = new ModuleBuilder(OrderManager::loadManager());
            /** @var ModulePayment $module */
            $module = $moduleBuilder(['class' => "\\common\\modules\\orderPayment\\" . ModuleHelper::getModuleConfig()->getModuleName()]);
            $module->finalizeCheckout((int) $tmpOrder->child_id, $response->getPaymentTransaction()->getPaymentId());
            return ['success' => \true, 'redirect_url' => $this->getSuccessUrl($tmpOrder)];
        }
        return [];
    }
    protected function validateAccess(): bool
    {
        $paymentId = \Yii::$app->request->get('paymentId', \Yii::$app->request->get('hostedCheckoutId'));
        $customerId = \Yii::$app->user->getId();
        if (!$customerId) {
            return \false;
        }
        $response = CheckoutAPI::get()->payment((string) platform::currentId())->getPaymentOutcome(PaymentId::parse($paymentId), \Yii::$app->request->get('RETURNMAC'), \Yii::$app->request->get('merchantReference'));
        if (!$response->isSuccessful()) {
            return \false;
        }
        $tmpOrder = TmpOrder::getARModel()->where(['orders_id' => $response->getPaymentTransaction()->getMerchantReference()])->one();
        if (!$tmpOrder || $tmpOrder->customers_id !== $customerId) {
            return \false;
        }
        return \true;
    }
    /**
     * @param $paymentId
     * @return string
     */
    protected function getCheckOutcomeUrl($paymentId): string
    {
        return \Yii::$app->urlManager->createAbsoluteUrl([ModuleHelper::addModuleNamePrefix('checkout-return/payment-outcome'), 'paymentId' => $paymentId, 'RETURNMAC' => \Yii::$app->request->get('RETURNMAC'), 'merchantReference' => \Yii::$app->request->get('merchantReference')]);
    }
    /**
     * @param $tmpOrder
     * @return string
     */
    protected function getSuccessUrl($tmpOrder): string
    {
        return \Yii::$app->urlManager->createAbsoluteUrl(['checkout/success', 'order_id' => $tmpOrder->child_id]);
    }
}
