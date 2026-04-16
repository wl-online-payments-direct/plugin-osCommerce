<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\BootstrapComponent as CoreBootstrapComponent;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Connection\ConnectionConfigEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Disconnect\DisconnectTime;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\LogSettingsEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\PayByLinkSettingsEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\GeneralSettings\PaymentSettingsConfigEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Monitoring\MonitoringLog;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Monitoring\WebhookLog;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentLink\PaymentLinkEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentMethod\PaymentMethodConfigEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\PaymentTransactionEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction\PaymentTransactionLockEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\ProductTypes\ProductTypeEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Tokens\TokenEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\SingleInstance;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Branding\Brand\ActiveBrandProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\GeneralSettings\Repositories\LogSettingsRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\ActiveConnectionProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\Repositories\TokensRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Encryption\Encryptor;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Language\LanguageService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Logo\LogoUrlService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Metadata\MetadataProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\ShopOrderService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Stores\StoreService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Version\VersionService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\MonitoringLogRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\WebhookLogRepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentProductService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Configuration\ConfigEntity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Configuration\Configuration;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Http\HttpClient;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\RepositoryRegistry;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Serializer\Serializer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\Process;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\QueueItem;
use common\modules\orderPayment\WOP\OnlinePayments\Entities\PayByLinkHash;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Proxies\GithubProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\BaseRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\BaseRepositoryWithConditionalDelete;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\MonitoringLogsRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\PaymentTransactionLocksRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\PaymentTransactionsRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\ProductTypesRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\QueueRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\TokensRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\WebhookLogsRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Services\DataAccess\Tokens\TokensRepository as DataAccessTokensRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Domain\Repositories\MonitoringLogRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Domain\Repositories\WebhookLogRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Integration\ConfigService;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Integration\LoggerService;
use common\modules\orderPayment\WOP\OnlinePayments\Services\PaymentLink\PayByLinkHashService;
class BootstrapComponent extends CoreBootstrapComponent
{
    public static function boot(string $moduleConfigFile, string $brandConfigFile): void
    {
        ServiceRegister::registerService(ModuleConfig::class, new SingleInstance(static function () use ($moduleConfigFile) {
            return new ModuleConfig($moduleConfigFile);
        }));
        static::bootstrap(static function () {
            return ModuleHelper::getModuleConfig()->getBrand();
        }, $brandConfigFile);
    }
    protected static function initServices(): void
    {
        parent::initServices();
        ServiceRegister::registerService(Serializer::class, function () {
            return new JsonSerializer();
        });
        ServiceRegister::registerService(Installer::class, new SingleInstance(static function () {
            return new Installer();
        }));
        ServiceRegister::registerService(ShopLoggerAdapter::class, new SingleInstance(static function () {
            return new LoggerService();
        }));
        ServiceRegister::registerService(Encryptor::class, new SingleInstance(static function () {
            return new Integration\Encryptor();
        }));
        ServiceRegister::registerService(MetadataProviderInterface::class, function () {
            return new Integration\MetadataProvider(ServiceRegister::getService(Configuration::class));
        });
        ServiceRegister::registerService(StoreService::class, function () {
            return new Integration\StoreService();
        });
        ServiceRegister::registerService(LogoUrlService::class, function () {
            return new Integration\LogoUrlService();
        });
        ServiceRegister::registerService(ShopPaymentService::class, function () {
            return new Integration\ShopPaymentService();
        });
        ServiceRegister::registerService(ShopOrderService::class, function () {
            return new Integration\ShopOrderService();
        });
        ServiceRegister::registerService(LanguageService::class, function () {
            return new Integration\LanguageService();
        });
        ServiceRegister::registerService(ModuleVisibilityService::class, function () {
            return new ModuleVisibilityService();
        });
        ServiceRegister::registerService(PaymentProductService::class, function () {
            return new Integration\PaymentProductService();
        });
        ServiceRegister::registerService(Configuration::class, function () {
            return ConfigService::getInstance();
        });
        ServiceRegister::registerService(ModuleRestrictionsService::class, function () {
            return new ModuleRestrictionsService();
        });
        ServiceRegister::registerService(VersionService::class, function () {
            return new Integration\VersionService(ServiceRegister::getService(GithubProxy::class));
        });
        ServiceRegister::registerService(PayByLinkHashService::class, function () {
            return new PayByLinkHashService(RepositoryRegistry::getRepository(PayByLinkHash::getClassName()), ServiceRegister::getService(Encryptor::class));
        });
    }
    protected static function initProxies(): void
    {
        parent::initProxies();
        ServiceRegister::registerService(GithubProxy::class, function () {
            return new GithubProxy(ServiceRegister::getService(HttpClient::class), 'https://api.github.com');
        });
    }
    protected static function initRepositories(): void
    {
        parent::initRepositories();
        RepositoryRegistry::registerRepository(Process::getClassName(), BaseRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::getClassName(), BaseRepository::getClassName());
        RepositoryRegistry::registerRepository(ConnectionConfigEntity::getClassName(), BaseRepositoryWithConditionalDelete::getClassName());
        RepositoryRegistry::registerRepository(PaymentMethodConfigEntity::getClassName(), BaseRepositoryWithConditionalDelete::getClassName());
        RepositoryRegistry::registerRepository(PaymentSettingsConfigEntity::getClassName(), BaseRepositoryWithConditionalDelete::getClassName());
        RepositoryRegistry::registerRepository(LogSettingsEntity::getClassName(), BaseRepositoryWithConditionalDelete::getClassName());
        RepositoryRegistry::registerRepository(DisconnectTime::getClassName(), BaseRepositoryWithConditionalDelete::getClassName());
        RepositoryRegistry::registerRepository(PayByLinkSettingsEntity::getClassName(), BaseRepositoryWithConditionalDelete::getClassName());
        RepositoryRegistry::registerRepository(PaymentLinkEntity::getClassName(), BaseRepositoryWithConditionalDelete::getClassName());
        RepositoryRegistry::registerRepository(ProductTypeEntity::getClassName(), ProductTypesRepository::getClassName());
        RepositoryRegistry::registerRepository(PaymentTransactionEntity::getClassName(), PaymentTransactionsRepository::getClassName());
        RepositoryRegistry::registerRepository(PaymentTransactionLockEntity::getClassName(), PaymentTransactionLocksRepository::getClassName());
        RepositoryRegistry::registerRepository(TokenEntity::getClassName(), TokensRepository::getClassName());
        RepositoryRegistry::registerRepository(QueueItem::getClassName(), QueueRepository::getClassName());
        RepositoryRegistry::registerRepository(MonitoringLog::getClassName(), MonitoringLogsRepository::getClassName());
        RepositoryRegistry::registerRepository(WebhookLog::getClassName(), WebhookLogsRepository::getClassName());
        RepositoryRegistry::registerRepository(PayByLinkHash::getClassName(), BaseRepositoryWithConditionalDelete::getClassName());
        ServiceRegister::registerService(TokensRepositoryInterface::class, new SingleInstance(static function () {
            /** @var ConditionallyDeletes $repository */
            $repository = RepositoryRegistry::getRepository(TokenEntity::class);
            return new DataAccessTokensRepository($repository, StoreContext::getInstance());
        }));
        ServiceRegister::registerService(WebhookLogRepositoryInterface::class, new SingleInstance(static function () {
            return new WebhookLogRepository(RepositoryRegistry::getRepository(WebhookLog::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class), ServiceRegister::getService(LogSettingsRepositoryInterface::class));
        }));
        ServiceRegister::registerService(MonitoringLogRepositoryInterface::class, new SingleInstance(static function () {
            return new MonitoringLogRepository(RepositoryRegistry::getRepository(MonitoringLog::class), StoreContext::getInstance(), ServiceRegister::getService(ActiveConnectionProvider::class), ServiceRegister::getService(ActiveBrandProviderInterface::class), ServiceRegister::getService(LogSettingsRepositoryInterface::class));
        }));
    }
}
