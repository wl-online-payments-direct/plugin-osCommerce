<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Helpers;

use backend\design\orders\PaymentExtraInfo;
use common\classes\modules\ModuleBuilder;
use common\classes\modules\ModulePayment;
use common\classes\Order;
use common\classes\TmpOrder;
use common\models\Orders;
use common\models\OrdersPayment;
use common\helpers\Html;
use common\helpers\Translation;
use common\services\OrderManager;
use DateTime;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\AdminConfigController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\ConnectionController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\GeneralSettingsController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\LanguageController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\MonitoringController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\OrderStatusesController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\PaymentsController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\RestrictionsController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\StateController;
use common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers\VersionController;
use common\modules\orderPayment\WOP\OnlinePayments\Common\Assets\CommonAsset;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\OrderAPI\OrderAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Response\TranslatableErrorResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ProductTypesAPI\Response\ProductTypesListResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentLinks\PaymentLinkRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ProductTypes\ProductType;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\AdminAPI\Response\PaymentLinkResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Entities\PayByLinkHash;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Checkout\CartProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Installer;
use common\modules\orderPayment\WOP\OnlinePayments\Services\PaymentLink\PayByLinkHashService;
use yii\base\ActionEvent;
use yii\base\Application;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\base\WidgetEvent;
use yii\db\ActiveRecord;
use yii\web\JqueryAsset;
use yii\web\View;
/**
 * Class AdminConfigHelper.
 *
 * @package OnlinePayments\Helpers
 */
class AdminConfigHelper
{
    private Application $app;
    private function __construct(Application $app)
    {
        $this->app = $app;
    }
    public static function bootstrap(Application $app): void
    {
        if ($app->id !== 'app-backend') {
            return;
        }
        (new self($app))->boot();
    }
    private function boot(): void
    {
        $this->app->controllerMap = array_merge($this->app->controllerMap, [ModuleHelper::addModuleNamePrefix('admin-config') => ['class' => AdminConfigController::class], ModuleHelper::addModuleNamePrefix('version') => ['class' => VersionController::class], ModuleHelper::addModuleNamePrefix('connection') => ['class' => ConnectionController::class], ModuleHelper::addModuleNamePrefix('state') => ['class' => StateController::class], ModuleHelper::addModuleNamePrefix('payments') => ['class' => PaymentsController::class], ModuleHelper::addModuleNamePrefix('general-settings') => ['class' => GeneralSettingsController::class], ModuleHelper::addModuleNamePrefix('languages') => ['class' => LanguageController::class], ModuleHelper::addModuleNamePrefix('order-statuses') => ['class' => OrderStatusesController::class], ModuleHelper::addModuleNamePrefix('monitoring') => ['class' => MonitoringController::class], ModuleHelper::addModuleNamePrefix('restrictions') => ['class' => RestrictionsController::class]]);
        $this->app->on(Application::EVENT_BEFORE_ACTION, function (ActionEvent $event) {
            if ($event->action->id === 'index' && $event->action->controller->id === 'modules') {
                $this->app->getView()->on(View::EVENT_END_BODY, function (Event $event) {
                    $this->extendAdminModulesManagement($event);
                });
            }
            if ($event->action->id === 'productedit' && $event->action->controller->id === 'categories') {
                $this->app->getView()->on(View::EVENT_END_BODY, function (Event $event) {
                    $this->extendAdminProductEdit($event);
                });
            }
            if ($event->action->id === 'product-submit' && $event->action->controller->id === 'categories' && $this->app->getRequest()->post(ModuleHelper::getModuleConfig()->getModuleName())) {
                $this->handleProductTypeSubmit();
            }
        });
        $this->app->on(Application::EVENT_BEFORE_ACTION, function (ActionEvent $event) {
            // Register void amount handler on order pages
            if ($event->action->controller->id === 'orders') {
                $this->app->getView()->on(View::EVENT_END_BODY, function (Event $event) {
                    $this->registerOrderActionsHandlers($event);
                });
            }
            if ($event->action->id === 'payment-list' && $event->action->controller->id === 'orders') {
                $this->app->getView()->on(View::EVENT_END_BODY, function (Event $event) {
                    $this->registerOrderActionsMaxAmounts($event);
                });
            }
            if ($event->action->id === 'order-edit' && $event->action->controller->id === 'editor') {
                $this->app->getView()->on(View::EVENT_END_BODY, function (Event $event) {
                    $this->registerOrderEditHandlers($event);
                });
            }
        });
        Event::on(Orders::class, ActiveRecord::EVENT_AFTER_INSERT, [$this, 'onOrderUpdated']);
        Event::on(Orders::class, ActiveRecord::EVENT_AFTER_UPDATE, [$this, 'onOrderUpdated']);
        $this->app->on(Application::EVENT_BEFORE_ACTION, function (ActionEvent $event) {
            if ($event->action->id === 'revert-file' && $event->action->controller->id === 'install' && \false !== strpos($this->app->getRequest()->post('name'), ModuleHelper::getModuleConfig()->getModuleName())) {
                /** @var Installer $installer */
                $installer = ServiceRegister::getService(Installer::class);
                $installer->remove();
            }
        });
        $this->app->on(Application::EVENT_BEFORE_REQUEST, function (Event $event) {
            if ($this->app->getRequest()->getPathInfo() !== 'modules/edit' || $this->app->getRequest()->get('module') !== ModuleHelper::getModuleConfig()->getModuleName()) {
                return;
            }
            $event->handled = \true;
            $adminConfigUrl = $this->app->urlManager->createUrl([ModuleHelper::addModuleNamePrefix('admin-config'), 'platform_id' => $this->app->request->get('platform_id', $this->app->request->post('platform_id'))]);
            $this->app->getResponse()->redirect($adminConfigUrl);
        });
        Event::on(PaymentExtraInfo::class, Widget::EVENT_AFTER_RUN, [$this, 'appendPayByLinkWidget']);
    }
    public function onOrderUpdated()
    {
        $controllerId = \Yii::$app->controller->id;
        $actionId = \Yii::$app->controller->action->id;
        if ($actionId !== 'checkout' && $controllerId !== 'editor' || tep_session_name() !== 'tlAdminID') {
            return;
        }
        $data = \Yii::$app->request->post();
        $getData = \Yii::$app->request->get();
        if ($data['payment'] !== ModuleHelper::getModuleConfig()->getModuleName()) {
            return;
        }
        // create temp order if it doesn't exist
        $manager = OrderManager::loadManager();
        $orderId = $getData['orders_id'] ?? 0;
        if (!$orderId) {
            $order = Order::getARModel()->where(['basket_id' => $manager->get('cart')->basketID])->one();
            $orderId = $order->orders_id;
        }
        $model = TmpOrder::getARModel()->where(['child_id' => $orderId])->one();
        if (!$model) {
            $moduleBuilder = new ModuleBuilder($manager);
            /** @var ModulePayment $module */
            $module = $moduleBuilder(['class' => "\\common\\modules\\orderPayment\\" . ModuleHelper::getModuleConfig()->getModuleName()]);
            $module->saveOrderBySettings();
            $model = TmpOrder::getARModel()->where(['child_id' => $orderId])->one();
        }
        $existingLink = AdminAPI::get()->paymentLinks($manager->getPlatformId())->get($model->orders_id);
        if ($existingLink->isSuccessful() && $existingLink->getRedirectUrl()) {
            return;
        }
        // create payment link
        $expirationDate = $data[ModuleHelper::addModuleNamePrefix('payment', '_')]['expiration_date'];
        $date = new DateTime($expirationDate);
        $security = new \yii\base\Security();
        $secretKey = \Yii::$app->params['secKey.backend'];
        $hash = $security->hashData($model->orders_id . '|' . $model->platform_id, $secretKey);
        $paymentLink = AdminAPI::get()->paymentLinks($manager->getPlatformId())->create(new PaymentLinkRequest(new CartProvider($manager), tep_catalog_href_link(ModuleHelper::addModuleNamePrefix('payment-link-return'), 'hash=' . $hash . '&merchantReference=' . $model->orders_id), $date));
        if ($paymentLink->isSuccessful()) {
            $this->savePayByLinkHash($hash, $model, $date);
        }
    }
    public function extendAdminModulesManagement(Event $event): void
    {
        /* @var $view View */
        $view = $event->sender;
        $moduleName = ModuleHelper::getModuleConfig()->getModuleName();
        $adminConfigUrl = $this->app->urlManager->createUrl(ModuleHelper::addModuleNamePrefix('admin-config'));
        $view->registerJs("opAdminConfigInit('{$moduleName}', '{$adminConfigUrl}')");
        $view->registerJsFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Backend/assets/js/admin-config-initializer.js'), ['appendTimestamp' => \true, 'depends' => [JqueryAsset::class]]);
    }
    public function registerOrderEditHandlers(Event $event): void
    {
        $orderId = (int) $this->app->getRequest()->get('orders_id');
        if (!$orderId) {
            $manager = OrderManager::loadManager();
            $order = Order::getARModel()->where(['basket_id' => $manager->get('cart')->basketID])->one();
            $orderId = $order->orders_id;
        }
        if (!$orderId) {
            return;
        }
        $order = Orders::findOne($orderId);
        $tmpOrder = TmpOrder::getARModel()->where(['child_id' => $orderId])->one();
        $paymentLink = AdminAPI::get()->paymentLinks($order->platform_id)->get($tmpOrder->orders_id);
        if ($order && $tmpOrder && $paymentLink->getRedirectUrl()) {
            /* @var $view View */
            $view = $event->sender;
            $this->renderPaymentLink($paymentLink->getRedirectUrl(), $view);
        }
        // if order is first saved, and then the Pay by Link is chosen
        // as payment method and order is saved again
        // the pay by link section will not be rendered because
        // order save is performed using Ajax request
        // so we need js code that will detect this event and reload the page
        $moduleCode = ModuleHelper::getModuleConfig()->getModuleName();
        $view = $event->sender;
        $view->registerJs(<<<JS
        (function(\$) {
            \$(document).ajaxComplete(function(event, xhr, settings) {
                if (xhr.responseJSON && xhr.responseJSON.order_id && xhr.responseJSON.type === 'success') {
                    var selectedPayment = \$('input[name="payment"]:checked').val();
                    if (selectedPayment && selectedPayment.indexOf('{$moduleCode}') !== -1) {
                        window.location.reload();
                    }
                }
            });
        })(\$);
        JS);
        if (!$this->isOrderPayedWithUs($orderId)) {
            return;
        }
        /* @var $view View */
        $view = $event->sender;
        $view->registerJsFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Backend/assets/js/admin-order-edit-disabler.js'), ['appendTimestamp' => \true, 'depends' => [JqueryAsset::class]]);
    }
    /**
     * Register order handlers JavaScript on order pages
     *
     * @param Event $event
     *
     * @return void
     *
     * @throws InvalidConfigException
     */
    public function registerOrderActionsHandlers(Event $event): void
    {
        if (!$this->isOrderPayedWithUs((int) $this->app->getRequest()->get('orders_id'))) {
            return;
        }
        Translation::init('payment');
        /* @var $view View */
        $view = $event->sender;
        $moduleCode = ModuleHelper::getModuleConfig()->getModuleName();
        Translation::init('admin/orders');
        $amountErrorLabel = Html::encode(TEXT_REFUND_AMOUNT_ERROR);
        $view->registerJs("{$moduleCode}_AdminOrderExtender('{$moduleCode}', '{$amountErrorLabel}')");
        $view->registerJsFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Backend/assets/js/admin-order-extender.js'), ['appendTimestamp' => \true, 'depends' => [JqueryAsset::class]]);
    }
    public function registerOrderActionsMaxAmounts(Event $event): void
    {
        $orderId = (string) $this->app->getRequest()->get('oID');
        if (!$orderId) {
            return;
        }
        $moduleCode = ModuleHelper::getModuleConfig()->getModuleName();
        $tmpOrderModel = TmpOrder::getARModel()->where(['child_id' => $orderId])->one();
        if (!$tmpOrderModel || strpos($tmpOrderModel->payment_class, $moduleCode) !== 0) {
            return;
        }
        $orderDetailsResponse = OrderAPI::get()->orders($tmpOrderModel->platform_id)->getDetails($tmpOrderModel->orders_id);
        if (!$orderDetailsResponse->isSuccessful()) {
            return;
        }
        $orderDetails = $orderDetailsResponse->getOrderDetails();
        $maxVoidAmount = $orderDetails->getCancel()->getAvailable()->getPriceInCurrencyUnits();
        $maxRefundAmount = $orderDetails->getRefund()->getAvailable()->getPriceInCurrencyUnits();
        $maxCaptureAmount = $orderDetails->getCapture()->getAvailable()->getPriceInCurrencyUnits();
        $currencyCode = $orderDetails->getAmount()->getCurrency()->getIsoCode();
        $currencies = \Yii::$container->get('currencies');
        $formattedRefundAmount = $currencies->format($maxRefundAmount, \false, $currencyCode);
        $isPartial = count($orderDetails->getPayments()) > 1;
        // htmlspecialchars is needed because of apostrophes in translations
        $fraudResult = htmlspecialchars(ModuleHelper::getConstantValue('TEXT_FRAUD_RESULT'), \ENT_QUOTES, 'UTF-8');
        $liability = htmlspecialchars(ModuleHelper::getConstantValue('TEXT_LIABILITY'), \ENT_QUOTES, 'UTF-8');
        $threeDSExemption = htmlspecialchars(ModuleHelper::getConstantValue('TEXT_THREE_DS_EXEMPTION_TYPE'), \ENT_QUOTES, 'UTF-8');
        $partialPaymentRefund = htmlspecialchars(ModuleHelper::getConstantValue('TEXT_PARTIAL_PAYMENT_REFUND'), \ENT_QUOTES, 'UTF-8');
        echo "<div id='{$moduleCode}_max_void_amount' data-amount='{$maxVoidAmount}'></div>";
        echo "<div id='{$moduleCode}_max_refund_amount' data-amount='{$maxRefundAmount}'></div>";
        echo "<div id='{$moduleCode}_max_capture_amount' data-amount='{$maxCaptureAmount}'></div>";
        echo "<div id='{$moduleCode}_fraud_result' data-translation='{$fraudResult}'></div>";
        echo "<div id='{$moduleCode}_liability' data-translation='{$liability}'></div>";
        echo "<div id='{$moduleCode}_three_ds_exemption' data-translation='{$threeDSExemption}'></div>";
        echo "<div id='{$moduleCode}_is_partial_payment' data-partial='{$isPartial}'></div>";
        echo "<div id='{$moduleCode}_partial_payment_refund' data-translation='{$partialPaymentRefund}'></div>";
        echo "<div id='{$moduleCode}_formatted_refund_amount' data-amount='{$formattedRefundAmount}'></div>";
    }
    public function extendAdminProductEdit(Event $event): void
    {
        /* @var $view View */
        $view = $event->sender;
        $idProduct = (string) $this->app->getRequest()->get('pID');
        $result = AdminAPI::get()->productTypes()->list($idProduct);
        if (!$result->isSuccessful()) {
            return;
        }
        Translation::init('payment');
        echo $this->getProductEditTabContent($result);
        $moduleName = ModuleHelper::getModuleConfig()->getModuleName();
        $tabTitle = Html::encode(ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_CONFIG_TAB_TITLE'));
        $view->registerJs("{$moduleName}_AdminProductExtender('{$moduleName}', '{$tabTitle}')");
        $view->registerJsFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Backend/assets/js/admin-product-extender.js'), ['appendTimestamp' => \true, 'depends' => [JqueryAsset::class]]);
    }
    private function getProductEditTabContent(ProductTypesListResponse $response): string
    {
        $moduleName = ModuleHelper::getModuleConfig()->getModuleName();
        $brand = ModuleHelper::getModuleConfig()->getBrand();
        $version = ModuleHelper::getModuleConfig()->getVersion();
        $asset = CommonAsset::register($this->app->view);
        $logo = Html::img("{$asset->baseUrl}/images/{$brand}-small.svg", ['class' => 'img-fluid rounded-start', 'alt' => 'logo', 'style' => 'width: 40px;']);
        $title = ModuleHelper::getModuleConfig()->getName();
        $description = ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_CONFIG_HEADER_DESCRIPTION');
        $configTitle = ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_CONFIG_TITLE');
        $configDescription = sprintf(ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_CONFIG_DESCRIPTION'), ModuleHelper::getModuleConfig()->getName());
        $productTypeLabel = ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_LABEL');
        $productTypes = ['' => Html::encode(ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_OPTION_NONE_LABEL')), (string) ProductType::foodAndDrink() => Html::encode(ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_OPTION_FOOD_AND_DRINK_LABEL')), (string) ProductType::homeAndGarden() => Html::encode(ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_OPTION_HOME_AND_GARDEN_LABEL')), (string) ProductType::giftAndFlowers() => Html::encode(ModuleHelper::getConstantValue('TEXT_PRODUCT_TYPE_OPTION_GIFT_AND_FLOWERS_LABEL'))];
        $productTypeOptions = '';
        foreach ($productTypes as $productType => $label) {
            if ($productType === (string) $response->getSelectedProductType()) {
                $productTypeOptions .= "<option value=\"{$productType}\" selected=\"selected\">{$label}</option>";
                continue;
            }
            $productTypeOptions .= "<option value=\"{$productType}\">{$label}</option>";
        }
        return <<<EOD
        <div class="tab-pane topTabPane tabbable-custom" id="tab_{$moduleName}_gift_card" style="display: none">
            <div class="widget box box-no-shadow m-0">
                <div class="widget-content">        
                    <div class="card h-100 pb-5">
                        <div class="row g-0 pt-3">
                            <div class="col-auto d-flex align-items-center ps-3">
                                {$logo}
                            </div>
                            <div class="col-md-8 d-flex align-items-center">
                                <div class="card-body py-0">
                                    <h3 class="card-title panel-title fw-bold mb-0">{$title}</h3>
                                    <p class="card-text mb-0"><small class="text-muted">{$version} by {$title}</small></p>
                              </div>
                            </div>      
                        </div>
                        <div class="row g-0">
                            <div class="card-body">
                                <p class="card-text">{$description}</p>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="card-body">
                                <div class="col-md-12">
                                    <h3 class="card-title panel-title fw-bold">{$configTitle}</h3>
                                    <p class="card-text">{$configDescription}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="card-body">
                                <div class="col-md-8">
                                    <label for="worldlineop[product_type]" class="form-label">{$productTypeLabel}</label>
                                    <select id="{$moduleName}[product_type]" name="{$moduleName}[product_type]" class="form-select">
                                        {$productTypeOptions}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        EOD;
    }
    private function handleProductTypeSubmit(): void
    {
        $idProduct = (string) $this->app->getRequest()->post('products_id');
        if (!$idProduct) {
            return;
        }
        $form = $this->app->getRequest()->post(ModuleHelper::getModuleConfig()->getModuleName());
        try {
            AdminAPI::get()->productTypes()->save($idProduct, ProductType::parse((string) $form['product_type']));
        } catch (\Throwable $e) {
            AdminAPI::get()->productTypes()->delete($idProduct);
        }
    }
    private function isOrderPayedWithUs(int $orderId): bool
    {
        if ($orderId <= 0) {
            return \false;
        }
        return OrdersPayment::find()->where(['orders_payment_order_id' => $orderId])->andWhere(['orders_payment_module' => ModuleHelper::getModuleConfig()->getModuleName()])->exists();
    }
    public function appendPayByLinkWidget(WidgetEvent $event): void
    {
        $paymentLink = $this->getLink();
        if (!$paymentLink->isSuccessful() || !$paymentLink->getRedirectUrl()) {
            return;
        }
        $moduleCode = ModuleHelper::getModuleConfig()->getModuleName();
        $linkUrl = Html::encode($paymentLink->getRedirectUrl());
        // Append to the widget result
        $event->result .= <<<HTML
        <div class="{$moduleCode}-pay-by-link-section" style="margin-top: 15px;">
            <span><strong>Worldline Pay by Link</strong></span>
            <div style="margin-top: 5px;">
                <input type="text" class="form-control" value="{$linkUrl}" readonly id="{$moduleCode}-payment-link-url" style="margin-bottom: 5px;">
                <button type="button" class="btn btn-default btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('{$moduleCode}-payment-link-url').value).then(function(){alert('Copied!')})">
                    Copy Link
                </button>
            </div>
        </div>
        HTML;
    }
    /**
     * @param string $url
     * @param View $view
     *
     * @return void
     */
    public function renderPaymentLink(string $url, View $view): void
    {
        $widgetHtml = Html::encode($url);
        $moduleCode = ModuleHelper::getModuleConfig()->getModuleName();
        $moduleName = ModuleHelper::getModuleConfig()->getName();
        $title = sprintf(ModuleHelper::getConstantValue('TEXT_PAY_BY_LINK_TITLE'), $moduleName);
        $copy = ModuleHelper::getConstantValue('TEXT_COPY_PAYMENT_LINK');
        $copiedMessage = ModuleHelper::getConstantValue('TEXT_PAYMENT_LINK_COPIED');
        echo <<<HTML
        <div id="{$moduleCode}-pay-by-link-widget" style="display:none;">
            <div class="pay-by-link-modules-box" style="margin-top: 15px; width: 49%; margin-left: auto;">
                <div class="widget box box-no-shadow">
                    <div class="widget-header">
                        <h4><i class="icon-credit-card"></i>{$title}</h4>
                        <div class="toolbar no-padding">
                            <div class="btn-group">
                                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content">
                        <div class="pay-by-link-content" style="padding: 10px 15px;">
                            <div style="display: flex; gap: 10px;">
                                <input type="text" class="form-control" value="{$widgetHtml}" readonly id="{$moduleCode}-edit-payment-link-url" style="flex: 1;">
                                <button type="button" class="btn btn-default" id="{$moduleCode}-copy-btn">
                                    {$copy}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
        $view->registerJs(<<<JS
        (function(\$) {
            var widget = \$('#{$moduleCode}-pay-by-link-widget');
            var modulesBox = \$('.modules-box');
            if (modulesBox.length && widget.length) {
                modulesBox.append(widget.find('.pay-by-link-modules-box'));
                widget.remove();
            }
        
            \$('#{$moduleCode}-copy-btn').on('click', function() {
                navigator.clipboard.writeText(document.getElementById('{$moduleCode}-edit-payment-link-url').value).then(function() {
                    alertMessage('<div class="alert-message">{$copiedMessage}</div>', 'success');
                });
            });
        })(\$);
        JS);
    }
    /**
     * @param string $hash
     * @param $model
     * @param DateTime $date
     *
     * @return void
     *
     * @throws \Exception
     */
    public function savePayByLinkHash(string $hash, $model, DateTime $date): void
    {
        $payByLinkHash = new PayByLinkHash();
        $payByLinkHash->setHash($hash);
        $payByLinkHash->setOrderId($model->orders_id);
        $payByLinkHash->setExpiresAt($date);
        StoreContext::doWithStore($model->platform_id, function () use ($payByLinkHash) {
            /** @var PayByLinkHashService $service */
            $service = ServiceRegister::getService(PayByLinkHashService::class);
            $service->save($payByLinkHash);
        });
    }
    /**
     * @return PaymentLinkResponse|TranslatableErrorResponse
     */
    public function getLink()
    {
        $orderId = (int) $this->app->getRequest()->get('orders_id');
        $order = Orders::findOne($orderId);
        $tmpOrder = TmpOrder::getARModel()->where(['child_id' => $orderId])->one();
        return AdminAPI::get()->paymentLinks($order->platform_id)->get($tmpOrder->orders_id);
    }
}
