<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Frontend\Controllers;

use common\classes\Order;
use common\classes\platform;
use common\classes\TmpOrder;
use common\components\google\widgets\GoogleTagmanger;
use common\helpers\Hooks;
use common\helpers\Output;
use common\helpers\Translation;
use common\services\OrderManager;
use frontend\design\Info;
use common\modules\orderPayment\WOP\OnlinePayments\Common\Controllers\ControllerViewPathResolver;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\CheckoutAPI\CheckoutAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Services\PaymentLink\PayByLinkHashService;
use Yii;
/**
 * Class PaymentLinkReturnController
 *
 * @package OnlinePayments\Frontend\Controllers
 */
class PaymentLinkReturnController extends CheckoutReturnController
{
    use ControllerViewPathResolver;
    /**
     * Overridden method from CheckoutController used for Payment Link successful payments.
     * The only change is the validation process - we cannot check
     * if the user is set up in session, so we check if pay by link hash is valid.
     *
     * @return array|false|mixed|string|string[]
     *
     * @throws \Throwable
     */
    public function actionSuccess()
    {
        global $breadcrumb, $platform_code, $cart;
        Translation::init('checkout');
        Translation::init('checkout/login');
        Translation::init('checkout/success');
        $hash = Yii::$app->request->get('hash');
        $merchantReference = Yii::$app->request->get('merchantReference');
        if (!$hash) {
            return \false;
        }
        /** @var PayByLinkHashService $service */
        $service = ServiceRegister::getService(PayByLinkHashService::class);
        $payByLinkHash = StoreContext::doWithStore((string) platform::currentId(), function () use ($service, $merchantReference) {
            return $service->get($merchantReference);
        });
        if ($payByLinkHash->getHash() !== $hash) {
            return \false;
        }
        StoreContext::doWithStore((string) platform::currentId(), function () use ($merchantReference, $service) {
            $service->delete($merchantReference);
        });
        $this->layout = 'main.tpl';
        $tmpOrder = TmpOrder::getARModel()->where(['orders_id' => $merchantReference])->one();
        $customer_id = $tmpOrder->customers_id;
        if (tep_session_is_registered('platform_code')) {
            $platform_code = '';
        }
        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);
        $breadcrumb->add(NAVBAR_TITLE);
        $order_info = \false;
        $cart->order_id = 0;
        $order_id = intval(Yii::$app->request->getQueryParam('order_id', 0));
        if ($order_id) {
            $order_info = tep_db_fetch_array(tep_db_query("SELECT orders_id, orders_status " . "FROM " . TABLE_ORDERS . " " . "WHERE orders_id='" . (int) $order_id . "' AND customers_id = '" . (int) $customer_id . "'"));
        }
        if (!is_array($order_info)) {
            $orders_query = tep_db_query("select orders_id, orders_status " . "from " . TABLE_ORDERS . " " . "where customers_id = '" . (int) $customer_id . "' " . "order by /*date_purchased*/ orders_id desc limit 1");
            if (tep_db_num_rows($orders_query)) {
                $order_info = tep_db_fetch_array($orders_query);
            }
        }
        $order_info_data = array('order_id' => 0, 'print_order_href' => Info::isAdmin() ? '1111' : '', 'order' => \false);
        if (is_array($order_info)) {
            $manager = OrderManager::loadManager();
            $order = $manager->getOrderInstanceWithId(Order::class, $order_info['orders_id']);
            GoogleTagmanger::setEvent('checkout');
            $order->info['order_id'] = $order_info['orders_id'];
            $order_info_data = array('order_id' => $order_info['orders_id'], 'print_order_href' => tep_href_link('account/invoice', Output::get_all_get_params(array('order_id')) . 'orders_id=' . $order_info['orders_id'], 'SSL'), 'order' => $order, 'manager' => $manager);
        }
        GoogleTagmanger::setEvent('orderSuccess');
        foreach (Hooks::getList('checkout/success', '') as $filename) {
            include $filename;
        }
        foreach (Hooks::getList('frontend/checkout/success', '') as $filename) {
            include $filename;
        }
        if (defined('common\modules\orderPayment\WOP\AUTO_LOGOFF_GUEST_ON_SUCCESS') && AUTO_LOGOFF_GUEST_ON_SUCCESS == 'True' && !Yii::$app->user->isGuest) {
            $customer = Yii::$app->user->getIdentity();
            if ($customer->opc_temp_account == 1) {
                Yii::$app->settings->clear(['languages_id']);
                Yii::$app->user->getIdentity()->logoffCustomer();
                $cart->reset();
                unset($order_info_data['print_order_href']);
            }
        }
        return $this->render('success.tpl', array_merge(['products' => '', 'continue_href' => tep_href_link(FILENAME_DEFAULT, '', 'NONSSL'), 'params' => $order_info_data, 'order' => $order_info_data['order']], $order_info_data));
    }
    protected function validateAccess(): bool
    {
        $paymentId = Yii::$app->request->get('paymentId', Yii::$app->request->get('hostedCheckoutId'));
        $hash = Yii::$app->request->get('hash');
        $merchantReference = Yii::$app->request->get('merchantReference');
        if (!$hash) {
            return \false;
        }
        $payByLinkHash = StoreContext::doWithStore((string) platform::currentId(), function () use ($merchantReference) {
            /** @var PayByLinkHashService $service */
            $service = ServiceRegister::getService(PayByLinkHashService::class);
            return $service->get($merchantReference);
        });
        if (!$payByLinkHash || $payByLinkHash->getHash() !== $hash) {
            return \false;
        }
        $response = CheckoutAPI::get()->payment((string) platform::currentId())->getPaymentOutcome(PaymentId::parse($paymentId), Yii::$app->request->get('RETURNMAC'), $merchantReference);
        if (!$response->isSuccessful()) {
            return \false;
        }
        $tmpOrder = TmpOrder::getARModel()->where(['orders_id' => $response->getPaymentTransaction()->getMerchantReference()])->one();
        if (!$tmpOrder) {
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
        return Yii::$app->urlManager->createAbsoluteUrl([ModuleHelper::addModuleNamePrefix('payment-link-return/payment-outcome'), 'paymentId' => $paymentId, 'RETURNMAC' => Yii::$app->request->get('RETURNMAC'), 'merchantReference' => Yii::$app->request->get('merchantReference'), 'hash' => Yii::$app->request->get('hash')]);
    }
    protected function getSuccessUrl($tmpOrder): string
    {
        return Yii::$app->urlManager->createAbsoluteUrl([ModuleHelper::addModuleNamePrefix('payment-link-return/success'), 'order_id' => $tmpOrder->child_id, 'hash' => Yii::$app->request->get('hash'), 'merchantReference' => Yii::$app->request->get('merchantReference')]);
    }
}
