<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\Proxies\ConnectionProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\CancelProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\CaptureProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Order\Proxies\RefundProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\BackgroundProcesses\AutoCaptureCheckListener;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\BackgroundProcesses\TransactionStatusCheckListener;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\BackgroundProcesses\WaitPaymentOutcomeProcessStarter;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\HostedCheckoutProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\HostedTokenizationProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\PaymentLinksProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\PaymentMethodProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\PaymentsProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\SurchargeProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Connection\ConnectionConfigEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Connection\ConnectionConfigRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Disconnect\DisconnectRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Disconnect\DisconnectTime;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\LogSettingsEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\LogSettingsRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\PayByLinkSettingsEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\PayByLinkSettingsRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\PaymentSettingsConfigEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\PaymentSettingsRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Maintenance\TaskCleanupRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Monitoring\MonitoringLog;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Monitoring\MonitoringLogRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Monitoring\WebhookLog;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Monitoring\WebhookLogRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentLink\PaymentLinkEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentLink\PaymentLinkRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentMethod\PaymentMethodConfigEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentMethod\PaymentMethodConfigRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\AuthorizedTransactionsRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\PaymentTransactionEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\PaymentTransactionLockEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\PaymentTransactionRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\PendingTransactionsRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\ProductTypes\ProductTypeEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\ProductTypes\ProductTypeRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Tokens\TokenEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Tokens\TokensRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Disconnect\DisconnectTaskEnqueuer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\LogCleanup\LogCleanupTaskService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Maintenance\TaskCleanupListener;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk\MerchantClientFactory;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk\WebhookTransformer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Time\TimeProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Branding\Brand\ActiveBrandProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Branding\Brand\ActiveBrandProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ConnectionAPI\Controller\ConnectionController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\GeneralSettingsAPI\Controller\GeneralSettingsController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\IntegrationAPI\Controller\IntegrationController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\LanguageAPI\Controller\LanguageController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\MonitoringAPI\Controller\LogsController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Controller\PaymentController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ProductTypesAPI\Controller\ProductTypesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\StoreAPI\Controller\StoreController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\VersionsAPI\Controller\VersionController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Connection\ConnectionService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Connection\Proxies\ConnectionProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Disconnect\DisconnectService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Disconnect\Repositories\DisconnectRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\GeneralSettings\GeneralSettingsService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\GeneralSettings\Repositories\LogSettingsRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\GeneralSettings\Repositories\PayByLinkSettingsRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\GeneralSettings\Repositories\PaymentSettingsRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Monitoring\MonitoringLogsService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Monitoring\WebhookLogsService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Payment\PaymentService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\ProductTypes\ProductTypeService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\ProductTypes\Repositories\ProductTypeRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\ActiveConnectionProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionConfigRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Disconnect\DisconnectTaskEnqueuerInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\Repositories\TokensRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Encryption\Encryptor;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Language\LanguageService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Logo\LogoUrlService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Metadata\MetadataProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\ShopOrderService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Stores\StoreService as IntegrationStoreService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Version\VersionService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\LogCleanup\LogCleanupListener;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\LogCleanup\LogCleanupTaskServiceInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\MonitoringLogRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\WebhookLogRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\OrderStatusMapping\StatusMappingService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\Repositories\PaymentTransactionRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentLinks\Repositories\PaymentLinkRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentProductService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\Repositories\PaymentConfigRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\ThreeDSSettingsService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Stores\StoreService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Time\TimeProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Webhook\Transformers\WebhookTransformerInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\ApiFacades\CancelAPI\Controller\CancelController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\ApiFacades\CaptureAPI\Controller\CaptureController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\ApiFacades\OrdersAPI\Controller\OrderController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\ApiFacades\RefundAPI\Controller\RefundController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Proxies\CancelProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Proxies\CaptureProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Proxies\RefundProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Services\Cancel\CancelService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Services\Capture\CaptureService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Services\Order\OrderService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Order\Services\Refund\RefundService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\AdminAPI\Controller\PaymentLinksController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Controller\HostedCheckoutController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Controller\HostedTokenizationController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Controller\PaymentController as CheckoutAPIPaymentController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Controller\PaymentMethodsController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\WebhooksAPI\Controller\WebhooksController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\BackgroundProcesses\WaitPaymentOutcomeProcess;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\BackgroundProcesses\WaitPaymentOutcomeProcessStarterInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\HostedCheckoutProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\HostedTokenizationProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\PaymentLinksProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\PaymentMethodProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\PaymentsProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Proxies\SurchargeProxyInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Repositories\PaymentMethodConfigRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\HostedCheckout\HostedCheckoutService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\HostedTokenization\HostedTokenizationService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\Payment\StatusUpdateService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\PaymentLinks\PaymentLinksService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\PaymentLinks\PaymentLinkTransactionService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\PaymentMethod\PaymentMethodService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\Webhooks\WebhookService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\BootstrapComponent as BaseBootstrapComponent;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\RepositoryRegistry;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\QueueItem;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\QueueService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Utility\Events\EventBus;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Utility\TimeProvider as InfrastructureTimeProvider;
/**
 * Class BootstrapComponent
 *
 * @package OnlinePayments\Core\Bootstrap
 */
class BootstrapComponent extends BaseBootstrapComponent
{
    public static function bootstrap(callable $activeBrandResolver, string $brandConfigFile): void
    {
        static::init();
        ServiceRegister::registerService(ActiveBrandProviderInterface::class, new SingleInstance(static function () use ($brandConfigFile, $activeBrandResolver) {
            return new ActiveBrandProvider($activeBrandResolver, $brandConfigFile);
        }));
    }
    public static function init(): void
    {
        parent::init();
        static::initControllers();
        static::initProxies();
        static::initRequestProcessors();
        static::initBackgroundProcesses();
    }
    /**
     * @return void
     */
    protected static function initServices(): void
    {
        parent::initServices();
        ServiceRegister::registerService(TimeProviderInterface::class, new SingleInstance(static function () {
            return new TimeProvider(ServiceRegister::getService(InfrastructureTimeProvider::class));
        }));
        ServiceRegister::registerService(PaymentMethodService::class, new SingleInstance(static function () {
            return new PaymentMethodService(ServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class), ServiceRegister::getService(ProductTypeRepositoryInterface::class), ServiceRegister::getService(PaymentMethodProxyInterface::class), ServiceRegister::getService(SurchargeProxyInterface::class));
        }));
        ServiceRegister::registerService(HostedTokenizationService::class, new SingleInstance(static function () {
            return new HostedTokenizationService(ServiceRegister::getService(HostedTokenizationProxyInterface::class), ServiceRegister::getService(PaymentsProxyInterface::class), ServiceRegister::getService(PaymentTransactionRepositoryInterface::class), ServiceRegister::getService(ThreeDSSettingsService::class), ServiceRegister::getService(PaymentSettingsRepositoryInterface::class), ServiceRegister::getService(TokensRepositoryInterface::class), ServiceRegister::getService(WaitPaymentOutcomeProcess::class), ServiceRegister::getService(LogoUrlService::class), ServiceRegister::getService(ActiveBrandProviderInterface::class), ServiceRegister::getService(PaymentMethodService::class));
        }));
        ServiceRegister::registerService(HostedCheckoutService::class, new SingleInstance(static function () {
            return new HostedCheckoutService(ServiceRegister::getService(HostedCheckoutProxyInterface::class), ServiceRegister::getService(PaymentTransactionRepositoryInterface::class), ServiceRegister::getService(TokensRepositoryInterface::class), ServiceRegister::getService(ThreeDSSettingsService::class), ServiceRegister::getService(PaymentSettingsRepositoryInterface::class), ServiceRegister::getService(ProductTypeRepositoryInterface::class), ServiceRegister::getService(PaymentMethodService::class), ServiceRegister::getService(PaymentProductService::class));
        }));
        ServiceRegister::registerService(WaitPaymentOutcomeProcess::class, new SingleInstance(static function () {
            return new WaitPaymentOutcomeProcess(ServiceRegister::getService(PaymentTransactionRepositoryInterface::class), ServiceRegister::getService(StatusUpdateService::class), ServiceRegister::getService(TimeProviderInterface::class), ServiceRegister::getService(WaitPaymentOutcomeProcessStarterInterface::class), ServiceRegister::getService(PaymentLinkRepositoryInterface::class), ServiceRegister::getService(PaymentLinkTransactionService::class));
        }));
        ServiceRegister::registerService(StatusUpdateService::class, new SingleInstance(static function () {
            return new StatusUpdateService(ServiceRegister::getService(PaymentTransactionRepositoryInterface::class), ServiceRegister::getService(PaymentsProxyInterface::class), ServiceRegister::getService(TokensRepositoryInterface::class), ServiceRegister::getService(HostedTokenizationProxyInterface::class), ServiceRegister::getService(TimeProviderInterface::class), ServiceRegister::getService(ShopOrderService::class), ServiceRegister::getService(StatusMappingService::class), ServiceRegister::getService(PaymentSettingsRepositoryInterface::class));
        }));
        ServiceRegister::registerService(ActiveConnectionProvider::class, new SingleInstance(static function () {
            return new ActiveConnectionProvider(ServiceRegister::getService(ConnectionConfigRepositoryInterface::class));
        }));
        ServiceRegister::registerService(MerchantClientFactory::class, new SingleInstance(static function () {
            return new MerchantClientFactory(ServiceRegister::getService(ActiveConnectionProvider::class), ServiceRegister::getService(ActiveBrandProviderInterface::class), ServiceRegister::getService(MetadataProviderInterface::class));
        }));
        ServiceRegister::registerService(ConnectionService::class, function () {
            return new ConnectionService(ServiceRegister::getService(ConnectionConfigRepositoryInterface::class), ServiceRegister::getService(ConnectionProxyInterface::class));
        });
        ServiceRegister::registerService(PaymentService::class, function () {
            return new PaymentService(ServiceRegister::getService(PaymentConfigRepositoryInterface::class), ServiceRegister::getService(LogoUrlService::class), ServiceRegister::getService(ActiveBrandProviderInterface::class), ServiceRegister::getService(PaymentProductService::class), ServiceRegister::getService(GeneralSettingsService::class), ServiceRegister::getService(LanguageService::class));
        });
        ServiceRegister::registerService(StoreService::class, function () {
            return new StoreService(ServiceRegister::getService(IntegrationStoreService::class), ServiceRegister::getService(ConnectionConfigRepositoryInterface::class));
        });
        ServiceRegister::registerService(GeneralSettingsService::class, function () {
            return new GeneralSettingsService(ServiceRegister::getService(ConnectionConfigRepositoryInterface::class), ServiceRegister::getService(LogSettingsRepositoryInterface::class), ServiceRegister::getService(PaymentSettingsRepositoryInterface::class), ServiceRegister::getService(IntegrationStoreService::class), ServiceRegister::getService(PayByLinkSettingsRepositoryInterface::class));
        });
        ServiceRegister::registerService(DisconnectService::class, function () {
            return new DisconnectService(ServiceRegister::getService(ShopPaymentService::class), ServiceRegister::getService(ConnectionConfigRepositoryInterface::class), ServiceRegister::getService(PaymentSettingsRepositoryInterface::class), ServiceRegister::getService(LogSettingsRepositoryInterface::class), ServiceRegister::getService(PaymentMethodConfigRepositoryInterface::class), ServiceRegister::getService(PayByLinkSettingsRepositoryInterface::class), ServiceRegister::getService(DisconnectTaskEnqueuerInterface::class));
        });
        ServiceRegister::registerService(ProductTypeService::class, new SingleInstance(static function () {
            return new ProductTypeService(ServiceRegister::getService(ProductTypeRepositoryInterface::class));
        }));
        ServiceRegister::registerService(WebhookService::class, new SingleInstance(static function () {
            return new WebhookService(ServiceRegister::getService(StatusUpdateService::class), ServiceRegister::getService(PaymentTransactionRepositoryInterface::class), ServiceRegister::getService(PaymentLinkTransactionService::class));
        }));
        ServiceRegister::registerService(StatusMappingService::class, new SingleInstance(static function () {
            return new StatusMappingService(ServiceRegister::getService(GeneralSettingsService::class));
        }));
        ServiceRegister::registerService(MonitoringLogsService::class, new SingleInstance(static function () {
            return new MonitoringLogsService(ServiceRegister::getService(MonitoringLogRepositoryInterface::class), ServiceRegister::getService(DisconnectRepositoryInterface::class));
        }));
        ServiceRegister::registerService(WebhookLogsService::class, new SingleInstance(static function () {
            return new WebhookLogsService(ServiceRegister::getService(WebhookLogRepositoryInterface::class), ServiceRegister::getService(PaymentsProxyInterface::class), ServiceRegister::getService(DisconnectRepositoryInterface::class), ServiceRegister::getService(ActiveBrandProviderInterface::class));
        }));
        ServiceRegister::registerService(PaymentLinksService::class, new SingleInstance(static function () {
            return new PaymentLinksService(ServiceRegister::getService(PaymentLinksProxyInterface::class), ServiceRegister::getService(ThreeDSSettingsService::class), ServiceRegister::getService(PaymentSettingsRepositoryInterface::class), ServiceRegister::getService(PayByLinkSettingsRepositoryInterface::class), ServiceRegister::getService(PaymentLinkRepositoryInterface::class), ServiceRegister::getService(PaymentTransactionRepositoryInterface::class), ServiceRegister::getService(PaymentMethodService::class), ServiceRegister::getService(PaymentProductService::class));
        }));
        ServiceRegister::registerService(DisconnectTaskEnqueuerInterface::class, new SingleInstance(static function () {
            return new DisconnectTaskEnqueuer(ServiceRegister::getService(DisconnectRepositoryInterface::class), ServiceRegister::getService(QueueService::class));
        }));
        ServiceRegister::registerService(WebhookTransformerInterface::class, function () {
            return new WebhookTransformer(ServiceRegister::getService(ActiveConnectionProvider::class));
        });
        ServiceRegister::registerService(OrderService::class, new SingleInstance(static function () {
            return new OrderService(ServiceRegister::getService(PaymentTransactionRepositoryInterface::class), ServiceRegister::getService(PaymentsProxyInterface::class));
        }));
        ServiceRegister::registerService(CaptureService::class, new SingleInstance(static function () {
            return new CaptureService(ServiceRegister::getService(CaptureProxyInterface::class));
        }));
        ServiceRegister::registerService(CancelService::class, new SingleInstance(static function () {
            return new CancelService(ServiceRegister::getService(CancelProxyInterface::class));
        }));
        ServiceRegister::registerService(RefundService::class, new SingleInstance(static function () {
            return new RefundService(ServiceRegister::getService(RefundProxyInterface::class));
        }));
        ServiceRegister::registerService(LogCleanupTaskServiceInterface::class, new SingleInstance(static function () {
            return new LogCleanupTaskService();
        }));
        ServiceRegister::registerService(PaymentProductService::class, new SingleInstance(static function () {
            return new PaymentProductService();
        }));
        ServiceRegister::registerService(ThreeDSSettingsService::class, new SingleInstance(static function () {
            return new ThreeDSSettingsService(ServiceRegister::getService(PaymentConfigRepositoryInterface::class));
        }));
        ServiceRegister::registerService(PaymentLinkTransactionService::class, new SingleInstance(static function () {
            return new PaymentLinkTransactionService(ServiceRegister::getService(PaymentLinksProxyInterface::class), ServiceRegister::getService(PaymentLinkRepositoryInterface::class), ServiceRegister::getService(PaymentTransactionRepositoryInterface::class));
        }));
    }
    /**
     * @return void
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();
        ServiceRegister::registerService(PaymentTransactionRepositoryInterface::class, new SingleInstance(static function () {
            return new PaymentTransactionRepository(RepositoryRegistry::getRepository(PaymentTransactionEntity::class), RepositoryRegistry::getRepository(PaymentTransactionLockEntity::class), StoreContext::getInstance(), ServiceRegister::getService(TimeProviderInterface::class));
        }));
        ServiceRegister::registerService(PendingTransactionsRepository::class, new SingleInstance(static function () {
            return new PendingTransactionsRepository(RepositoryRegistry::getRepository(PaymentTransactionEntity::class), ServiceRegister::getService(TimeProviderInterface::class));
        }));
        ServiceRegister::registerService(AuthorizedTransactionsRepository::class, new SingleInstance(static function () {
            return new AuthorizedTransactionsRepository(RepositoryRegistry::getRepository(PaymentTransactionEntity::class), ServiceRegister::getService(TimeProviderInterface::class));
        }));
        ServiceRegister::registerService(TokensRepositoryInterface::class, new SingleInstance(static function () {
            /** @var ConditionallyDeletes $repository */
            $repository = RepositoryRegistry::getRepository(TokenEntity::class);
            return new TokensRepository($repository, StoreContext::getInstance());
        }));
        ServiceRegister::registerService(PaymentMethodConfigRepositoryInterface::class, new SingleInstance(static function () {
            return new PaymentMethodConfigRepository(RepositoryRegistry::getRepository(PaymentMethodConfigEntity::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class));
        }));
        ServiceRegister::registerService(PaymentConfigRepositoryInterface::class, new SingleInstance(static function () {
            return new PaymentMethodConfigRepository(RepositoryRegistry::getRepository(PaymentMethodConfigEntity::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class));
        }));
        ServiceRegister::registerService(ConnectionConfigRepositoryInterface::class, new SingleInstance(static function () {
            return new ConnectionConfigRepository(RepositoryRegistry::getRepository(ConnectionConfigEntity::class), StoreContext::getInstance(), ServiceRegister::getService(Encryptor::class));
        }));
        ServiceRegister::registerService(PaymentSettingsRepositoryInterface::class, new SingleInstance(static function () {
            return new PaymentSettingsRepository(RepositoryRegistry::getRepository(PaymentSettingsConfigEntity::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class));
        }));
        ServiceRegister::registerService(LogSettingsRepositoryInterface::class, new SingleInstance(static function () {
            return new LogSettingsRepository(RepositoryRegistry::getRepository(LogSettingsEntity::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class));
        }));
        ServiceRegister::registerService(DisconnectRepositoryInterface::class, new SingleInstance(static function () {
            return new DisconnectRepository(StoreContext::getInstance(), RepositoryRegistry::getRepository(DisconnectTime::class));
        }));
        ServiceRegister::registerService(PayByLinkSettingsRepositoryInterface::class, new SingleInstance(static function () {
            return new PayByLinkSettingsRepository(RepositoryRegistry::getRepository(PayByLinkSettingsEntity::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class));
        }));
        ServiceRegister::registerService(PaymentLinkRepositoryInterface::class, new SingleInstance(static function () {
            return new PaymentLinkRepository(RepositoryRegistry::getRepository(PaymentLinkEntity::class), StoreContext::getInstance());
        }));
        ServiceRegister::registerService(ProductTypeRepositoryInterface::class, new SingleInstance(static function () {
            /** @var ConditionallyDeletes $repository */
            $repository = RepositoryRegistry::getRepository(ProductTypeEntity::class);
            return new ProductTypeRepository($repository);
        }));
        ServiceRegister::registerService(TaskCleanupRepository::class, new SingleInstance(static function () {
            /** @var QueueItemRepository $repository */
            $repository = RepositoryRegistry::getRepository(QueueItem::class);
            return new TaskCleanupRepository($repository);
        }));
        ServiceRegister::registerService(MonitoringLogRepositoryInterface::class, new SingleInstance(static function () {
            return new MonitoringLogRepository(RepositoryRegistry::getRepository(MonitoringLog::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class), ServiceRegister::getService(ActiveBrandProviderInterface::class), ServiceRegister::getService(LogSettingsRepositoryInterface::class));
        }));
        ServiceRegister::registerService(WebhookLogRepositoryInterface::class, new SingleInstance(static function () {
            return new WebhookLogRepository(RepositoryRegistry::getRepository(WebhookLog::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class), ServiceRegister::getService(LogSettingsRepositoryInterface::class));
        }));
    }
    /**
     * @return void
     */
    protected static function initControllers(): void
    {
        ServiceRegister::registerService(PaymentMethodsController::class, new SingleInstance(static function () {
            return new PaymentMethodsController(ServiceRegister::getService(PaymentMethodService::class), ServiceRegister::getService(HostedTokenizationService::class));
        }));
        ServiceRegister::registerService(HostedTokenizationController::class, new SingleInstance(static function () {
            return new HostedTokenizationController(ServiceRegister::getService(HostedTokenizationService::class));
        }));
        ServiceRegister::registerService(HostedCheckoutController::class, new SingleInstance(static function () {
            return new HostedCheckoutController(ServiceRegister::getService(HostedCheckoutService::class));
        }));
        ServiceRegister::registerService(CheckoutAPIPaymentController::class, new SingleInstance(static function () {
            return new CheckoutAPIPaymentController(ServiceRegister::getService(WaitPaymentOutcomeProcess::class), ServiceRegister::getService(PaymentTransactionRepositoryInterface::class));
        }));
        ServiceRegister::registerService(ConnectionController::class, new SingleInstance(static function () {
            return new ConnectionController(ServiceRegister::getService(ConnectionService::class));
        }));
        ServiceRegister::registerService(VersionController::class, new SingleInstance(static function () {
            return new VersionController(ServiceRegister::getService(VersionService::class));
        }));
        ServiceRegister::registerService(StoreController::class, new SingleInstance(static function () {
            return new StoreController(ServiceRegister::getService(StoreService::class));
        }));
        ServiceRegister::registerService(IntegrationController::class, new SingleInstance(static function () {
            return new IntegrationController(ServiceRegister::getService(ConnectionService::class));
        }));
        ServiceRegister::registerService(PaymentController::class, new SingleInstance(static function () {
            return new PaymentController(ServiceRegister::getService(PaymentService::class), ServiceRegister::getService(ShopPaymentService::class));
        }));
        ServiceRegister::registerService(LanguageController::class, new SingleInstance(static function () {
            return new LanguageController(ServiceRegister::getService(LanguageService::class));
        }));
        ServiceRegister::registerService(GeneralSettingsController::class, new SingleInstance(static function () {
            return new GeneralSettingsController(ServiceRegister::getService(GeneralSettingsService::class), ServiceRegister::getService(DisconnectService::class));
        }));
        ServiceRegister::registerService(ProductTypesController::class, new SingleInstance(static function () {
            return new ProductTypesController(ServiceRegister::getService(ProductTypeService::class));
        }));
        ServiceRegister::registerService(WebhooksController::class, new SingleInstance(static function () {
            return new WebhooksController(ServiceRegister::getService(WebhookTransformerInterface::class), ServiceRegister::getService(WebhookService::class), ServiceRegister::getService(WebhookLogsService::class));
        }));
        ServiceRegister::registerService(LogsController::class, new SingleInstance(static function () {
            return new LogsController(ServiceRegister::getService(MonitoringLogsService::class), ServiceRegister::getService(WebhookLogsService::class));
        }));
        ServiceRegister::registerService(PaymentLinksController::class, new SingleInstance(static function () {
            return new PaymentLinksController(ServiceRegister::getService(PaymentLinksService::class));
        }));
        ServiceRegister::registerService(OrderController::class, new SingleInstance(static function () {
            return new OrderController(ServiceRegister::getService(OrderService::class));
        }));
        ServiceRegister::registerService(CaptureController::class, new SingleInstance(static function () {
            return new CaptureController(ServiceRegister::getService(CaptureService::class));
        }));
        ServiceRegister::registerService(CancelController::class, new SingleInstance(static function () {
            return new CancelController(ServiceRegister::getService(CancelService::class));
        }));
        ServiceRegister::registerService(RefundController::class, new SingleInstance(static function () {
            return new RefundController(ServiceRegister::getService(RefundService::class));
        }));
    }
    protected static function initProxies(): void
    {
        ServiceRegister::registerService(PaymentMethodProxyInterface::class, new SingleInstance(static function () {
            return new PaymentMethodProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(PaymentsProxyInterface::class, new SingleInstance(static function () {
            return new PaymentsProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(HostedCheckoutProxyInterface::class, new SingleInstance(static function () {
            return new HostedCheckoutProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(HostedTokenizationProxyInterface::class, new SingleInstance(static function () {
            return new HostedTokenizationProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(ConnectionProxyInterface::class, new SingleInstance(static function () {
            return new ConnectionProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(PaymentLinksProxyInterface::class, new SingleInstance(static function () {
            return new PaymentLinksProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(SurchargeProxyInterface::class, new SingleInstance(static function () {
            return new SurchargeProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(CaptureProxyInterface::class, new SingleInstance(static function () {
            return new CaptureProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(CancelProxyInterface::class, new SingleInstance(static function () {
            return new CancelProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
        ServiceRegister::registerService(RefundProxyInterface::class, new SingleInstance(static function () {
            return new RefundProxy(ServiceRegister::getService(MerchantClientFactory::class));
        }));
    }
    protected static function initRequestProcessors(): void
    {
    }
    protected static function initBackgroundProcesses(): void
    {
        ServiceRegister::registerService(WaitPaymentOutcomeProcessStarterInterface::class, new SingleInstance(static function () {
            return new WaitPaymentOutcomeProcessStarter(ServiceRegister::getService(AsyncProcessService::class), StoreContext::getInstance());
        }));
        ServiceRegister::registerService(TransactionStatusCheckListener::class, new SingleInstance(static function () {
            return new TransactionStatusCheckListener(ServiceRegister::getService(QueueService::class), ServiceRegister::getService(TimeProviderInterface::class));
        }));
        ServiceRegister::registerService(AutoCaptureCheckListener::class, new SingleInstance(static function () {
            return new AutoCaptureCheckListener(ServiceRegister::getService(QueueService::class), ServiceRegister::getService(TimeProviderInterface::class));
        }));
    }
    /**
     * @return void
     */
    protected static function initEvents()
    {
        parent::initEvents();
        /** @var EventBus $eventBus */
        $eventBus = ServiceRegister::getService(EventBus::class);
        $eventBus->when(TickEvent::class, static function () {
            /** @var TransactionStatusCheckListener $listener */
            $listener = ServiceRegister::getService(TransactionStatusCheckListener::class);
            $listener->handle();
        });
        $eventBus->when(TickEvent::class, static function () {
            /** @var AutoCaptureCheckListener $listener */
            $listener = ServiceRegister::getService(AutoCaptureCheckListener::class);
            $listener->handle();
        });
        $eventBus->when(TickEvent::class, static function () {
            (new TaskCleanupListener())->handle();
        });
        $eventBus->when(TickEvent::class, static function () {
            (new LogCleanupListener(ServiceRegister::getService(LogCleanupTaskServiceInterface::class)))->handle();
        });
    }
}
