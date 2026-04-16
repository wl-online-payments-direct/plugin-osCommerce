<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\classes\platform_config;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Metadata\Metadata;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Metadata\MetadataProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
class MetadataProvider implements MetadataProviderInterface
{
    private ConfigService $configService;
    /**
     * @param ConfigService $configService
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }
    public function getMetadata(): Metadata
    {
        $platformConfig = new platform_config(StoreContext::getInstance()->getStoreId());
        return new Metadata('osCommerce', $this->configService->getIntegrationVersion(), '', $this->configService->getPluginVersion(), $platformConfig->getCatalogBaseUrl());
    }
}
