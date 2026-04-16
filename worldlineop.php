<?php

namespace common\modules\orderPayment;

use common\classes\modules\ModulePayment;
use common\classes\modules\ModuleSortOrder;
use common\classes\modules\ModuleStatus;
use common\classes\modules\PaymentTokensInterface;
use common\classes\modules\TransactionalInterface;
use common\classes\Order;
use common\classes\TmpOrder;
use common\components\View;
use common\helpers\Language;
use common\helpers\OrderPayment as OrderPaymentHelper;
use common\models\OrdersPayment;
use common\services\PaymentTransactionManager;
use frontend\design\Info;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\OrderAPI\OrderAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\CheckoutAPI\CheckoutAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Branding\Brand\ActiveBrandProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\GeneralSettings\GeneralSettingsService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PayByLinkSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentSettings;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedCheckout\HostedCheckoutSessionRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\PaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Logo\LogoUrlService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\ShopOrderService as CoreShopOrderService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\PaymentId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\StatusCode;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentProductId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response\PaymentMethodsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Checkout\CartProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Installer;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Integration\ShopOrderService;
use common\modules\orderPayment\WOP\OnlinePayments\Services\osCommerce\CancelService;
use common\modules\orderPayment\WOP\OnlinePayments\Services\osCommerce\CaptureService;
use common\modules\orderPayment\WOP\OnlinePayments\Services\osCommerce\RefundService;
use yii\helpers\Html;
use yii\web\Response;
class worldlineop extends ModulePayment implements PaymentTokensInterface, TransactionalInterface
{
    public function __construct()
    {
        $bootstrap = \Yii::createObject(\common\modules\orderPayment\WOP\Bootstrap::class);
        $bootstrap->bootstrap(\Yii::$app);
        $this->defaultTranslationArray = [ModuleHelper::getFullConstantName('TEXT_TITLE') => ModuleHelper::getModuleConfig()->getName(), ModuleHelper::getFullConstantName('TEXT_DESCRIPTION') => ModuleHelper::getModuleConfig()->getDescription(), ModuleHelper::getFullConstantName('TEXT_PAY_WITH_STORED_CARD') => 'Pay with my previously saved card %S', ModuleHelper::getFullConstantName('TEXT_PAYMENT_ERROR') => 'An error occurred while processing the payment.', ModuleHelper::getFullConstantName('TEXT_WAITING_TITLE') => 'Please wait while we are processing your payment.', ModuleHelper::getFullConstantName('TEXT_PENDING_TRANS_MSG') => 'The transaction has not been confirmed yet.', ModuleHelper::getFullConstantName('TEXT_PENDING_TRANS_DETAILS') => 'We suggest you contact our customer service using this link:', ModuleHelper::getFullConstantName('TEXT_PENDING_TRANS_INSTRUCTIONS') => 'Please also provide us these transactions details:', ModuleHelper::getFullConstantName('TEXT_PAYMENT_ID_LABEL') => 'Payment ID:', ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_LABEL') => 'Product type:', ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_OPTION_NONE_LABEL') => 'None', ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_OPTION_FOOD_AND_DRINK_LABEL') => 'Food and Drink', ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_OPTION_HOME_AND_GARDEN_LABEL') => 'Home and Garden', ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_OPTION_GIFT_AND_FLOWERS_LABEL') => 'Gift and Flowers', ModuleHelper::getFullConstantName('TEXT_HOSTED_CHECKOUT_ID_LABEL') => 'Checkout ID:', ModuleHelper::getFullConstantName('TEXT_SURCHARGE_WARNING') => 'Please note that a surcharge may be applied to the amount you have to pay depending on the payment method you will use.', ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_CONFIG_TAB_TITLE') => 'Modules', ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_CONFIG_HEADER_DESCRIPTION') => ModuleHelper::getModuleConfig()->getDescription(), ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_CONFIG_TITLE') => 'Gift card specific configuration', ModuleHelper::getFullConstantName('TEXT_PRODUCT_TYPE_CONFIG_DESCRIPTION') => 'Please configure this section in case you accept gift cards as payment methods with %s', ModuleHelper::getFullConstantName('TEXT_FRAUD_RESULT') => 'Fraud result', ModuleHelper::getFullConstantName('TEXT_LIABILITY') => 'Liability', ModuleHelper::getFullConstantName('TEXT_THREE_DS_EXEMPTION_TYPE') => '3DS Exemption type', ModuleHelper::getFullConstantName('TEXT_PARTIAL_PAYMENT_REFUND') => 'Please note that the total paid amount (%s) will be refunded.', ModuleHelper::getFullConstantName('TEXT_COPY_PAYMENT_LINK') => 'Copy payment link', ModuleHelper::getFullConstantName('TEXT_PAY_BY_LINK_TITLE') => '%s Pay by Link', ModuleHelper::getFullConstantName('TEXT_PAYMENT_LINK_COPIED') => 'Payment link copied', ModuleHelper::getFullConstantName('TEXT_PAYMENT_LINK_EXPIRES_AT') => 'Payment link expires at'];
        parent::__construct();
        $this->code = ModuleHelper::getModuleConfig()->getModuleName();
        $this->title = ModuleHelper::getConstantValue('TEXT_TITLE');
        $this->description = ModuleHelper::getConstantValue('TEXT_DESCRIPTION');
        if (!defined(ModuleHelper::getFullConstantName('STATUS'))) {
            $this->enabled = \false;
            return;
        }
        $this->sort_order = ModuleHelper::getConstantValue('SORT_ORDER');
        $this->enabled = ModuleHelper::getConstantValue('STATUS') === 'True';
        $this->online = \true;
        $this->getCCCss();
    }
    public function install($platform_id)
    {
        parent::install($platform_id);
        /** @var Installer $installer */
        $installer = ServiceRegister::getService(Installer::class);
        $installer->install();
    }
    public function isOnline()
    {
        return \true;
    }
    public function hasToken(): bool
    {
        return parent::tokenAllowed();
    }
    public function useToken(): bool
    {
        return \true;
    }
    public function deleteToken($customersId, $token)
    {
        parent::deleteToken($customersId, $token);
        CheckoutAPI::get()->hostedTokenization($this->manager->getPlatformId())->deleteToken((string) $customersId, $token);
        return \true;
    }
    public function describe_status_key()
    {
        return new ModuleStatus(ModuleHelper::getFullConstantName('STATUS'), 'True', 'False');
    }
    public function describe_sort_key()
    {
        return new ModuleSortOrder(ModuleHelper::getFullConstantName('SORT_ORDER'));
    }
    public function configure_keys()
    {
        $configuration = [ModuleHelper::getFullConstantName('STATUS') => ['title' => sprintf('Enable %s Payment?', ModuleHelper::getModuleConfig()->getName()), 'value' => 'True', 'description' => '', 'sort_order' => 10, 'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'], ModuleHelper::getFullConstantName('SORT_ORDER') => ['title' => 'Sort order of display', 'sort_order' => 20, 'description' => 'Sort order of display. Lowest is displayed first.', 'value' => 10]];
        return $configuration;
    }
    public function selection()
    {
        // if available payment methods are retrieved from backoffice,
        // only Pay by Link should be returned if it is enabled
        if (Info::isAdmin()) {
            return $this->getPayByLink();
        }
        $response = CheckoutAPI::get()->paymentMethods((string) $this->manager->getPlatformId())->getAvailablePaymentMethods(new CartProvider($this->manager));
        if (!$response->isSuccessful()) {
            return \false;
        }
        $this->getCCCss();
        \Yii::$app->getView()->registerCssFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Frontend/assets/css/front.css'), ['appendTimestamp' => \true]);
        $fields = [];
        if ($response->getPaymentMethods()->isCardsTokenizationEnabled()) {
            \Yii::$app->getView()->registerJsFile($this->getLiveApiEndpoint() . '/hostedtokenization/js/client/tokenizer.min.js', ['position' => View::POS_HEAD]);
            \Yii::$app->getView()->registerJsFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Frontend/assets/js/tokenization.js'), ['appendTimestamp' => \true]);
            $tokensResponse = $response->getValidTokensResponse();
            $hostedTokenizationUrl = $tokensResponse ? $tokensResponse->getHostedTokenization()->getUrl() : $this->getHostedCheckoutUrl();
            \Yii::$app->getView()->registerJs($this->getTokenizationJS($hostedTokenizationUrl));
            $fields = [['field' => Html::tag('div', '', ['id' => ModuleHelper::addModuleNamePrefix('hosted-tokenization-container'), 'class' => 'hosted-tokenization-container'])], ['field' => Html::hiddenInput(ModuleHelper::addModuleNamePrefix('hosted_tokenization_id'))], ['field' => Html::hiddenInput(ModuleHelper::addModuleNamePrefix('color_depth'))], ['field' => Html::hiddenInput(ModuleHelper::addModuleNamePrefix('screen_height'))], ['field' => Html::hiddenInput(ModuleHelper::addModuleNamePrefix('screen_width'))], ['field' => Html::hiddenInput(ModuleHelper::addModuleNamePrefix('timezone_offset_utc_minutes'))], ['field' => Html::hiddenInput(ModuleHelper::addModuleNamePrefix('java_enabled'))]];
        }
        /** @var GeneralSettingsService $settingsService */
        $settingsService = ServiceRegister::getService(GeneralSettingsService::class);
        /** @var PaymentSettings $paymentSettings */
        $paymentSettings = StoreContext::doWithStore($this->manager->getPlatformId(), function () use ($settingsService) {
            return $settingsService->getPaymentSettings();
        });
        if ($paymentSettings->isApplySurcharge()) {
            \Yii::$app->getView()->registerJs($this->getSurchargeJs());
            //            $messageStack = \Yii::$container->get('message_stack');
            //            $messageStack->add(ModuleHelper::getConstantValue('TEXT_SURCHARGE_WARNING'), 'header', 'warning');
        }
        /** @var LogoUrlService $logoUrlService */
        $logoUrlService = ServiceRegister::getService(LogoUrlService::class);
        $methods = $this->getStoredTokensMethods($response);
        foreach ($response->getPaymentMethods()->toArray() as $paymentMethod) {
            $isCardsMethod = PaymentProductId::cards()->equals($paymentMethod->getProductId());
            if ($isCardsMethod && !$response->getPaymentMethods()->isCardsGroupingEnabled()) {
                continue;
            }
            $logoUrl = $logoUrlService->getLogoUrl($paymentMethod->getProductId());
            if ($paymentMethod->getProductId()->equals(PaymentProductId::hostedCheckout()) && !empty($paymentMethod->getAdditionalData()->getLogo())) {
                $logoUrl = $paymentMethod->getAdditionalData()->getLogo();
            }
            $addCCToId = $isCardsMethod || $paymentMethod->getProductId()->isCardBrand();
            $methods[] = ['id' => ModuleHelper::addModuleNamePrefix($paymentMethod->getProductId(), $addCCToId ? '_cc_' : '_'), 'module' => Html::encode($paymentMethod->getName()->getTranslationMessage($this->getCheckoutLanguage())) . Html::img($logoUrl, ['class' => $this->code . '-payment-method-logo', 'alt' => 'logo']), 'hide' => \false];
        }
        return ['id' => $this->code, 'module' => $this->title, 'methods' => $methods, 'fields' => $fields];
    }
    public function getCCCss()
    {
        parent::getCCCss();
        \Yii::$app->getView()->registerCss('img.' . $this->code . '-payment-method-logo {' . 'height: 20px;' . 'margin-left: 3px' . '}');
    }
    public function getSurchargeJs(): string
    {
        $warningLabel = ModuleHelper::getConstantValue('TEXT_SURCHARGE_WARNING');
        return <<<EOD
            window.{$this->code}_init_surcharge = function() {
                const surchargeMessage = `
                    <div class="box w-html_box messageBox">
                        <div class="info warning-message alert alert-warning" id="{$this->code}-surcharge-message">
                        {$warningLabel}
                        </div>
                    </div>
                `;
                
                \$('#payment_method').prepend(surchargeMessage);
                            
                const observer = new MutationObserver(function (mutationsList) {
                    for (const mutation of mutationsList) {
                        if (mutation.type === 'childList' && !\$('#{$this->code}-surcharge-message').length) {
                            \$('#payment_method').prepend(surchargeMessage);
                        }
                    }
                });
                observer.observe(\$('#payment_method').closest('form')[0], { childList: true, subtree: true });
            }
            
            if (typeof tl == 'function') {
                tl(function() {
                    try {
                        {$this->code}_init_surcharge();
                    }catch (e ) {}
                })
            }
        EOD;
    }
    public function getTokenizationJS(string $hostedTokenizationPageUrl = ''): string
    {
        $tokenizationContainer = ModuleHelper::addModuleNamePrefix('hosted-tokenization-container');
        $createSessionUrl = \Yii::$app->urlManager->createAbsoluteUrl(['callback/webhooks', 'set' => 'payment', 'action' => 'createTokenizationSession', 'module' => $this->code]);
        return <<<EOD
        window.{$this->code}_init_tokenization = function() {
            {$this->code}_tokenizer.init(
                '{$this->code}', {
                    "tokenizationContainer": "#{$tokenizationContainer}",
                    "urls": {
                        "createSession": "{$createSessionUrl}",
                        "hostedTokenizationPageUrl": "{$hostedTokenizationPageUrl}"
                    }
                }
            );
        }
        
        if (typeof checkout_payment_changed !== 'undefined') {
            checkout_payment_changed.set('{$this->code}_init_tokenization');
        }
        
        if (typeof tl == 'function') {
            tl(function() {
                try {
                    checkout_payment_changed.set('{$this->code}_init_tokenization');
                }catch (e ) {}
            })
        }
        
        // Initial trigger on page load to render tokenization for if tokenization method is selected
        try {
            if (\$('#payment_method input[name=payment]:checked').length) {
                \$('#payment_method input[name=payment]:checked').trigger('click');
            } else {
                \$('#payment_method input[name=payment]:first').trigger('click');
            }
        } catch (e) {
        }
        EOD;
    }
    protected function orderTypeBeforePayment(): string
    {
        return 'TmpOrder';
    }
    public function saveOrderBySettings()
    {
        // Make sure that we do not work with outdated or missing temp order reference
        if ($this->manager->has(ModuleHelper::addModuleNamePrefix('merchant_reference'))) {
            $tmpOrderId = $this->manager->get(ModuleHelper::addModuleNamePrefix('merchant_reference'));
            $model = TmpOrder::getARModel()->where(['orders_id' => $tmpOrderId])->one();
            if ($model && $model->child_id > 0) {
                $this->manager->remove(ModuleHelper::addModuleNamePrefix('merchant_reference'));
            }
        }
        // Reuse existing temp order if present
        if ($this->manager->has(ModuleHelper::addModuleNamePrefix('merchant_reference'))) {
            $tmpOrderId = $this->manager->get(ModuleHelper::addModuleNamePrefix('merchant_reference'));
            /** @var ?TmpOrder $tmpOrder */
            $tmpOrder = $this->manager->getParentToInstanceWithId(TmpOrder::class, $tmpOrderId);
            $tmpOrder->info = $this->manager->getOrderInstance()->info;
            $tmpOrder->totals = $this->manager->getOrderInstance()->totals;
            $tmpOrder->products = $this->manager->getOrderInstance()->products;
            $tmpOrder->customer = $this->manager->getOrderInstance()->customer;
            $tmpOrder->delivery = $this->manager->getOrderInstance()->delivery;
            $tmpOrder->billing = $this->manager->getOrderInstance()->billing;
            $tmpOrder->content_type = $this->manager->getOrderInstance()->content_type;
            $tmpOrder->tax_address = $this->manager->getOrderInstance()->tax_address;
            if (Info::isAdmin()) {
                $getData = \Yii::$app->request->get();
                $orderId = $getData['orders_id'] ?? 0;
                if (!$orderId) {
                    $orderId = $this->getOrderIdFromBasket();
                }
                $tmpOrderModel = TmpOrder::getARModel()->where(['orders_id' => $tmpOrderId])->one();
                $tmpOrderModel->child_id = $orderId ?? 0;
                $tmpOrderModel->save();
            }
            $tmpOrder->save_order($tmpOrder->getOrderId());
            $tmpOrder->save_details(\false);
            $tmpOrder->save_products(\false);
            return 'tmp' . $tmpOrder->getOrderId() . '-e' . $this->estimateOrderId();
        }
        $tmpOrderNumber = parent::saveOrderBySettings();
        if (empty($tmpOrderNumber)) {
            return null;
        }
        /** @var ?TmpOrder $tmpOrder */
        $tmpOrder = $this->manager->getParentToInstance(TmpOrder::class);
        if (!is_object($tmpOrder)) {
            return null;
        }
        if (Info::isAdmin()) {
            $getData = \Yii::$app->request->get();
            $orderId = $getData['orders_id'] ?? 0;
            if (!$orderId) {
                $orderId = $this->getOrderIdFromBasket();
            }
            $tmpOrderModel = TmpOrder::getARModel()->where(['orders_id' => $tmpOrder->order_id])->one();
            $tmpOrderModel->child_id = $orderId ?? 0;
            $tmpOrderModel->save();
        }
        $this->manager->set(ModuleHelper::addModuleNamePrefix('merchant_reference'), $tmpOrder->getOrderId());
        return $tmpOrderNumber;
    }
    public function before_process()
    {
        $tmpOrderNumber = $this->saveOrderBySettings();
        if (empty($tmpOrderNumber)) {
            tep_redirect($this->getCheckoutUrl(['payment_error' => $this->code], self::PAYMENT_PAGE));
        }
        $this->lockTmpOrder($this->manager->get(ModuleHelper::addModuleNamePrefix('merchant_reference')));
        $hostedTokenizationId = $this->getInput('hosted_tokenization_id', '');
        $selectedPaymentMethodId = $this->manager->getPayment();
        $selectedPaymentMethodData = explode('_token_', ModuleHelper::removeModuleNamePrefix($selectedPaymentMethodId, \false !== strpos($selectedPaymentMethodId, '_cc_') ? '_cc_' : '_'));
        $paymentProductId = $selectedPaymentMethodData[0];
        $tokenId = !empty($selectedPaymentMethodData[1]) ? $selectedPaymentMethodData[1] : null;
        if (!empty($hostedTokenizationId)) {
            $this->handleHostedTokenizationPayment($hostedTokenizationId, $tokenId);
        }
        $this->handleHostedCheckoutPayment($paymentProductId, $tokenId);
    }
    private function handleHostedTokenizationPayment(string $hostedTokenizationId, ?string $tokenId = null)
    {
        $response = CheckoutAPI::get()->hostedTokenization((string) $this->manager->getPlatformId())->pay(new PaymentRequest($hostedTokenizationId, new CartProvider($this->manager), \Yii::$app->urlManager->createAbsoluteUrl([ModuleHelper::addModuleNamePrefix('checkout-return')]), $tokenId));
        if (!$response->isSuccessful()) {
            tep_redirect($this->getCheckoutUrl(['payment_error' => $this->code], self::PAYMENT_PAGE));
        }
        if ($response->isRedirectRequired()) {
            tep_redirect($response->getRedirectUrl());
        }
        tep_redirect(\Yii::$app->urlManager->createAbsoluteUrl([ModuleHelper::addModuleNamePrefix('checkout-return'), 'paymentId' => (string) $response->getPaymentTransaction()->getPaymentId()]));
    }
    private function handleHostedCheckoutPayment(string $paymentProductId, ?string $tokenId = null)
    {
        $response = CheckoutAPI::get()->hostedCheckout((string) $this->manager->getPlatformId())->createSession(new HostedCheckoutSessionRequest(new CartProvider($this->manager), \Yii::$app->urlManager->createAbsoluteUrl([ModuleHelper::addModuleNamePrefix('checkout-return')]), $this->tryToParsePaymentProductId($paymentProductId), $tokenId));
        if (!$response->isSuccessful()) {
            tep_redirect($this->getCheckoutUrl(['payment_error' => $this->code], self::PAYMENT_PAGE));
        }
        tep_redirect($response->getRedirectUrl());
    }
    public function get_error()
    {
        return ['title' => ModuleHelper::getConstantValue('TEXT_PAYMENT_ERROR')];
    }
    public function finalizeCheckout(int $orderId, PaymentId $paymentId)
    {
        $order = $this->manager->getOrderInstanceWithId(Order::class, $orderId);
        $this->no_process($order);
        // Method $this->no_process_after($order, false) cannot be used.
        // In osCommerce 4.09 no_process_after always performs redirect,
        // which is incorrect for our case.
        $this->trackCredits();
        $this->manager->clearAfterProcess();
        foreach (\common\helpers\Hooks::getList('checkout/after-process', '') as $filename) {
            include $filename;
        }
        $this->manager->remove(ModuleHelper::addModuleNamePrefix('merchant_reference'));
    }
    public function getEncryptionKey()
    {
        return parent::getEncryptionKey();
    }
    public function getTokens($customersId, $tokenId = \false)
    {
        $savedTokens = parent::getTokens($customersId, $tokenId);
        $tokensMap = [];
        foreach ($savedTokens as $token) {
            if (\false === $token['token']) {
                continue;
            }
            $tokensMap[$token['token']] = $token;
        }
        return $tokensMap;
    }
    private function getStoredTokensMethods(PaymentMethodsResponse $response): array
    {
        if (!$response->getValidTokensResponse() || !$this->manager->isCustomerAssigned()) {
            return [];
        }
        /** @var LogoUrlService $logoUrlService */
        $logoUrlService = ServiceRegister::getService(LogoUrlService::class);
        $savedTokens = $this->getTokens($this->manager->getCustomerAssigned());
        $vaultName = ModuleHelper::getConstantValue('TEXT_PAY_WITH_STORED_CARD');
        if ($response->getPaymentMethods()->has(PaymentProductId::cards())) {
            $vaultName = $response->getPaymentMethods()->get(PaymentProductId::cards())->getAdditionalData()->getVaultTitles()->getTranslation($this->getCheckoutLanguage())->getMessage();
        }
        $methods = [];
        foreach ($response->getValidTokensResponse()->getTokens() as $token) {
            if (!array_key_exists($token->getTokenId(), $savedTokens)) {
                continue;
            }
            $logoUrl = $logoUrlService->getLogoUrl($token->getProductId());
            $methods[] = ['id' => ModuleHelper::addModuleNamePrefix($token->getProductId(), '_cc_') . '_token_' . $token->getTokenId(), 'module' => Html::encode("{$vaultName} {$token->getCardNumber()}") . Html::img($logoUrl, ['class' => $this->code . '-payment-method-logo', 'alt' => 'logo']), 'hide' => \false];
        }
        return $methods;
    }
    /**
     * Called on a checkout from frontend.
     * CallbackController will trigger this methods for each ajax url that starts
     * with callback/webhooks and module is set to our code
     */
    public function call_webhooks(): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $action = \Yii::$app->request->get('action');
        if ($action !== 'createTokenizationSession') {
            return ['success' => \false];
        }
        \Yii::$app->request->parsers = ['application/json' => \yii\web\JsonParser::class];
        $selectedPaymentMethodId = \Yii::$app->request->post('selectedPaymentMethod', '');
        $selectedPaymentMethodData = explode('_token_', ModuleHelper::removeModuleNamePrefix($selectedPaymentMethodId, '_cc_'));
        if (empty($selectedPaymentMethodData)) {
            return ['success' => \false];
        }
        if (!$this->manager->isInstance()) {
            global $cart;
            $this->manager->loadCart($cart);
            $this->manager->createOrderInstance(Order::class);
            $this->manager->checkoutOrder();
        }
        $paymentProductId = $selectedPaymentMethodData[0];
        return ['success' => \true, 'hostedTokenizationPageUrl' => $this->getHostedCheckoutUrl($paymentProductId)];
    }
    private function getHostedCheckoutUrl(string $paymentProductId = ''): string
    {
        $response = CheckoutAPI::get()->hostedTokenization((string) $this->manager->getPlatformId())->crate(new CartProvider($this->manager), empty($paymentProductId) ? null : PaymentProductId::parse($paymentProductId));
        if ($response && $response->isSuccessful() && $response->getHostedTokenization()) {
            return $response->getHostedTokenization()->getUrl();
        }
        return '';
    }
    private function getCheckoutLanguage(): ?string
    {
        return strtoupper(Language::get_language_code($this->manager->getCart()->language_id, \false));
    }
    private function getLiveApiEndpoint(): string
    {
        /** @var ActiveBrandProviderInterface $provider */
        $provider = ServiceRegister::getService(ActiveBrandProviderInterface::class);
        return $provider->getActiveBrand()->getLiveApiEndpoint();
    }
    private function getInput(string $key, $default = null)
    {
        $prefix = ModuleHelper::addModuleNamePrefix($key);
        if ($this->manager->has($prefix)) {
            return $this->manager->get($prefix);
        }
        $prefix = 'one_page_checkout_' . $prefix;
        if ($this->manager->has($prefix)) {
            return $this->manager->get($prefix);
        }
        return $default;
    }
    private function tryToParsePaymentProductId(string $paymentProductId): ?PaymentProductId
    {
        try {
            $parsedPaymentProductId = PaymentProductId::parse($paymentProductId);
            return !$parsedPaymentProductId->equals(PaymentProductId::hostedCheckout()) ? $parsedPaymentProductId : null;
        } catch (\Throwable $exception) {
            return null;
        }
    }
    public function parseTransactionDetails($details)
    {
        // There is no need to parse anything since we return parsed data in getTransactionDetails
        return $details;
    }
    public function getTransactionDetails($transaction_id, PaymentTransactionManager $tManager = null)
    {
        $order = $this->manager->getOrderInstance();
        $tmpOrderModel = TmpOrder::getARModel()->where(['child_id' => $order->getOrderId()])->one();
        if (!$tmpOrderModel || $tmpOrderModel->child_id <= 0) {
            throw new \RuntimeException('Failed to get transaction details');
        }
        $response = OrderAPI::get()->orders($order->info['platform_id'])->getDetails($tmpOrderModel->orders_id);
        if (!$response->isSuccessful()) {
            throw new \RuntimeException('Failed to get transaction details');
        }
        StoreContext::doWithStore($order->info['platform_id'], function () use ($response) {
            /** @var ShopOrderService $shopOrderService */
            $shopOrderService = ServiceRegister::getService(CoreShopOrderService::class);
            $shopOrderService->updateTransactions($response);
        });
        $amountCurrency = $response->getOrderDetails()->getAmount()->getCurrency()->getIsoCode();
        $currencyRate = 1;
        $currencies = \Yii::$container->get('currencies');
        if (array_key_exists($amountCurrency, $currencies->currencies)) {
            $currencyRate = $currencies->currencies[$amountCurrency]['value'];
        }
        return ['orders_payment_order_id' => $tmpOrderModel->child_id, 'orders_payment_module' => $this->code, 'orders_payment_module_name' => $this->title, 'orders_payment_status' => ModuleHelper::getPaymentTransactionStatus(StatusCode::parse($response->getStatusCode())), 'orders_payment_amount' => $response->getOrderDetails()->getAmount()->getPriceInCurrencyUnits(), 'orders_payment_currency' => $amountCurrency, 'orders_payment_currency_rate' => $currencyRate, 'orders_payment_snapshot' => json_encode(OrderPaymentHelper::getOrderPaymentSnapshot($order)), 'orders_payment_transaction_id' => $transaction_id, 'orders_payment_transaction_status' => $response->getStatusCode(), 'orders_payment_transaction_date' => date(\common\helpers\Date::DATABASE_DATETIME_FORMAT)];
    }
    public function canRefund($transaction_id): bool
    {
        try {
            $transaction = OrdersPayment::find()->where('orders_payment_transaction_id = :transaction_id', [':transaction_id' => $transaction_id])->one();
            if (!$transaction || in_array($transaction->orders_payment_transaction_status, ['PENDING_CAPTURE', 'CANCELLED', 'REJECTED', 'REJECTED_CAPTURE', 'CAPTURE_REQUESTED', 'REFUND_REQUESTED', 'REFUNDED'], \true)) {
                return \false;
            }
            $service = new RefundService();
            return $service->canRefund($transaction_id);
        } catch (\Exception $e) {
            return \false;
        }
    }
    public function refund($transaction_id, $amount = 0): bool
    {
        try {
            $service = new RefundService();
            return $service->refund($transaction_id, $amount);
        } catch (\Exception $e) {
            \Yii::error('Refund failed: ' . $e->getMessage(), $this->code);
            return \false;
        }
    }
    public function canVoid($transaction_id): bool
    {
        try {
            $transaction = OrdersPayment::find()->where('orders_payment_transaction_id = :transaction_id', [':transaction_id' => $transaction_id])->one();
            if (!$transaction || in_array($transaction->orders_payment_transaction_status, ['CAPTURED', 'CANCELLED', 'REJECTED', 'REJECTED_CAPTURE', 'CAPTURE_REQUESTED', 'REFUND_REQUESTED', 'REFUNDED'], \true) || !$this->isFirstTransaction($transaction->orders_payment_transaction_id)) {
                return \false;
            }
            $service = new CancelService();
            return $service->canCancel($transaction_id);
        } catch (\Exception $e) {
            return \false;
        }
    }
    public function void($transaction_id): bool
    {
        try {
            // Read amount from POST request (workaround to allow partial void without modifying core)
            $requestedAmount = (float) \Yii::$app->request->post('amount', 0);
            $service = new CancelService();
            return $service->cancel($transaction_id, $requestedAmount);
        } catch (\Exception $e) {
            \Yii::error('Void failed: ' . $e->getMessage(), $this->code);
            return \false;
        }
    }
    public function canCapture($transaction_id)
    {
        try {
            $transaction = OrdersPayment::find()->where('orders_payment_transaction_id = :transaction_id', [':transaction_id' => $transaction_id])->one();
            if (!$transaction || in_array($transaction->orders_payment_transaction_status, ['CAPTURED', 'CANCELLED', 'REJECTED', 'REJECTED_CAPTURE', 'CAPTURE_REQUESTED', 'REFUND_REQUESTED', 'REFUNDED'], \true) || !$this->isFirstTransaction($transaction->orders_payment_transaction_id)) {
                return \false;
            }
            $service = new CaptureService();
            return $service->canCapture($transaction_id);
        } catch (\Exception $e) {
            return \false;
        }
    }
    public function capture($transaction_id, $amount = 0): bool
    {
        try {
            $service = new CaptureService();
            return $service->capture($transaction_id, $amount);
        } catch (\Exception $e) {
            \Yii::error('Capture failed: ' . $e->getMessage(), $this->code);
            return \false;
        }
    }
    public function canReauthorize($transaction_id): bool
    {
        return \false;
    }
    public function reauthorize($transaction_id, $amount = 0): bool
    {
        return \false;
    }
    /**
     * @return array|false
     *
     * @throws \Exception
     */
    private function getPayByLink()
    {
        /** @var PayByLinkSettings $payByLinkSettings */
        $payByLinkSettings = StoreContext::doWithStore((string) $this->manager->getPlatformId(), static function () {
            /** @var GeneralSettingsService $settingsService */
            $settingsService = ServiceRegister::getService(GeneralSettingsService::class);
            return $settingsService->getPayByLinkSettings();
        });
        if (!$payByLinkSettings->isEnable()) {
            return \false;
        }
        $methods[] = ['id' => ModuleHelper::getModuleConfig()->getModuleName(), 'module' => Html::encode($payByLinkSettings->getTitle()), 'hide' => \false];
        $defaultDays = $payByLinkSettings->getExpirationTime()->getDays();
        $defaultDate = date('Y-m-d', strtotime("+{$defaultDays} days"));
        $minDate = date('Y-m-d');
        $maxDate = date('Y-m-d', strtotime('+6 months'));
        $orderId = \Yii::$app->request->get('orders_id');
        if (!$orderId) {
            $orderId = $this->getOrderIdFromBasket();
        }
        $tmpOrder = TmpOrder::getARModel()->where(['child_id' => $orderId])->one();
        $paymentLinkCreated = \false;
        if ($tmpOrder) {
            $paymentLink = AdminAPI::get()->paymentLinks($this->manager->getPlatformId())->get($tmpOrder->orders_id);
            $paymentLinkCreated = $paymentLink->isSuccessful() && !empty($paymentLink->getRedirectUrl());
        }
        $fields = [];
        if (!$paymentLinkCreated) {
            $fields = [['title' => ModuleHelper::getConstantValue('TEXT_PAYMENT_LINK_EXPIRES_AT'), 'field' => Html::input('date', ModuleHelper::addModuleNamePrefix('payment[expiration_date]', '_'), $defaultDate, ['min' => $minDate, 'max' => $maxDate])]];
        }
        return ['id' => $this->code, 'module' => $this->title, 'methods' => $methods, 'fields' => $fields];
    }
    /**
     * @return mixed
     */
    private function getOrderIdFromBasket()
    {
        $order = Order::getARModel()->where(['basket_id' => $this->manager->get('cart')->basketID])->one();
        return $order->orders_id;
    }
    private function isFirstTransaction(string $id): bool
    {
        if (strlen($id) > 10 && strpos($id, '000') !== \false) {
            return \true;
        }
        if (strlen($id) > 10 && strpos($id, '_0') !== \false) {
            return \true;
        }
        return \false;
    }
}
