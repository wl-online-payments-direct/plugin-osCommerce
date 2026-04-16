<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\models\Platforms;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Configuration\Configuration;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
class ConfigService extends Configuration
{
    private const INTEGRATION_NAME = 'osCommerce';
    public const ASYNC_CALL_METHOD = 'GET';
    /**
     * @inheritDoc
     */
    public function getIntegrationVersion(): string
    {
        if (file_exists('includes/version.php')) {
            include_once 'includes/version.php';
        }
        return PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
    }
    /**
     * @inheritDoc
     */
    public function getIntegrationName(): string
    {
        return self::INTEGRATION_NAME;
    }
    /**
     * @inheritDoc
     */
    public function getPluginVersion(): string
    {
        return ModuleHelper::getModuleConfig()->getVersion();
    }
    /**
     * @inheritDoc
     */
    public function getAsyncProcessUrl(string $guid): string
    {
        $baseUrl = $this->getPlatformBaseUrl();
        $route = ModuleHelper::addModuleNamePrefix('async-process');
        return rtrim($baseUrl, '/') . '/' . $route . '?guid=' . $guid;
    }
    /**
     * @inheritDoc
     */
    public function getPluginName(): string
    {
        return ModuleHelper::getModuleConfig()->getModuleName();
    }
    /**
     * Get platform base URL with multiple fallback methods
     *
     * @return string
     */
    private function getPlatformBaseUrl(): string
    {
        $platformId = \common\classes\platform::currentId();
        try {
            $platform = Platforms::findOne($platformId);
            if ($platform && !empty($platform->platform_url)) {
                $url = $platform->platform_url;
                if (strpos($url, 'http') !== 0) {
                    $url = 'https://' . $url;
                }
                return rtrim($url, '/');
            }
        } catch (\Exception $e) {
            // Continue to fallback
        }
        if (\Yii::$app->has('request')) {
            return \Yii::$app->request->getHostInfo();
        }
        return '';
    }
}
