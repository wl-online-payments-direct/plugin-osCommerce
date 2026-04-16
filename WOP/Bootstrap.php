<?php

namespace common\modules\orderPayment\WOP;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\AdminConfigHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Services\BootstrapComponent;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\log\FileTarget;
require_once __DIR__ . '/vendor/autoload.php';
class Bootstrap implements BootstrapInterface
{
    private bool $booted = \false;
    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        if (!$this->booted) {
            $this->boot($app);
            $this->booted = \true;
        }
    }
    private function boot($app)
    {
        if ($app instanceof Application) {
            BootstrapComponent::boot(__DIR__ . '/config.json', __DIR__ . '/brand.json');
            AdminConfigHelper::bootstrap($app);
            if ($app->id == 'app-frontend') {
                $app->controllerMap = \array_merge($app->controllerMap, [ModuleHelper::addModuleNamePrefix('webhooks') => ['class' => __NAMESPACE__ . '\OnlinePayments\Frontend\Controllers\WebhooksController'], ModuleHelper::addModuleNamePrefix('async-process') => ['class' => __NAMESPACE__ . '\OnlinePayments\Frontend\Controllers\AsyncProcessController'], ModuleHelper::addModuleNamePrefix('checkout-return') => ['class' => __NAMESPACE__ . '\OnlinePayments\Frontend\Controllers\CheckoutReturnController'], ModuleHelper::addModuleNamePrefix('payment-link-return') => ['class' => __NAMESPACE__ . '\OnlinePayments\Frontend\Controllers\PaymentLinkReturnController']]);
            }
        }
        if (isset($app->log)) {
            $moduleName = ModuleHelper::getModuleConfig()->getModuleName();
            $app->log->targets[$moduleName] = new FileTarget(['categories' => [$moduleName], 'logFile' => '@app/runtime/logs/' . $moduleName . '.log', 'levels' => ['error', 'warning', 'info', 'trace'], 'logVars' => [], 'exportInterval' => 1]);
        }
    }
}
