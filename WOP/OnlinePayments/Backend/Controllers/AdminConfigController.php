<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Common\Assets\CommonAsset;
use common\modules\orderPayment\WOP\OnlinePayments\Common\Controllers\ControllerViewPathResolver;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use yii\helpers\Url;
use yii\web\JqueryAsset;
class AdminConfigController extends ModulesController
{
    use ControllerViewPathResolver;
    public $acl = ['BOX_HEADING_MODULES', 'BOX_MODULES_PAYMENT', 'BOX_MODULES_PAYMENT_ONLINE'];
    public $selectedMenu = ['modules', 'modules?set=payment&type=online'];
    public function actionIndex()
    {
        $asset = CommonAsset::register(\Yii::$app->view);
        \Yii::$app->view->registerJsFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Backend/assets/admin-ui/js/index.js'), ['appendTimestamp' => \true, 'depends' => [JqueryAsset::class]]);
        \Yii::$app->view->registerCssFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Backend/assets/admin-ui/css/index.css'), ['appendTimestamp' => \true, 'depends' => [JqueryAsset::class]]);
        \Yii::$app->view->registerCssFile(ModuleHelper::getAdminAssetUrl('/OnlinePayments/Backend/assets/admin-ui/css/op-admin.css'), ['appendTimestamp' => \true, 'depends' => [JqueryAsset::class]]);
        $baseLogoUrl = $asset->baseUrl . '/images';
        $this->navigation[] = ['link' => \Yii::$app->urlManager->createUrl('modules/index'), 'title' => ModuleHelper::getModuleConfig()->getName()];
        return self::render('index', ['selected_platform_id' => $this->selected_platform_id, 'module' => ModuleHelper::getModuleConfig()->getModuleName(), 'urls' => $this->getUrls(), 'translations' => $this->getTranslations(), 'brand' => ['name' => ModuleHelper::getModuleConfig()->getBrand(), 'code' => ModuleHelper::getModuleConfig()->getBrand()], 'baseLogoUrl' => $baseLogoUrl]);
    }
    private function getUrls(): array
    {
        return ['connection' => ['getSettingsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('connection/get-connection')]) . '?storeId={storeId}', 'submitUrl' => Url::to([ModuleHelper::addModuleNamePrefix('connection/submit')]) . '?storeId={storeId}', 'webhooksUrl' => tep_catalog_href_link(ModuleHelper::addModuleNamePrefix('webhooks/process')) . '?storeId={storeId}'], 'stores' => ['storesUrl' => '', 'currentStoreUrl' => ''], 'integration' => ['stateUrl' => Url::to([ModuleHelper::addModuleNamePrefix('state/get-state')]) . '?storeId={storeId}'], 'version' => ['versionUrl' => Url::to([ModuleHelper::addModuleNamePrefix('version/get-version')])], 'payments' => ['getAvailablePaymentsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('payments/get-list')]) . '?storeId={storeId}', 'enableMethodUrl' => Url::to([ModuleHelper::addModuleNamePrefix('payments/enable')]) . '?storeId={storeId}', 'saveMethodConfigurationUrl' => Url::to([ModuleHelper::addModuleNamePrefix('payments/save-method')]) . '?storeId={storeId}&methodId={methodId}', 'getMethodConfigurationUrl' => Url::to([ModuleHelper::addModuleNamePrefix('payments/get')]) . '?storeId={storeId}&methodId={methodId}', 'getLanguagesUrl' => Url::to([ModuleHelper::addModuleNamePrefix('languages/get-languages')]) . '?storeId={storeId}'], 'settings' => ['getGeneralSettingsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('general-settings/get-settings')]) . '?storeId={storeId}', 'getPaymentStatusesUrl' => Url::to([ModuleHelper::addModuleNamePrefix('order-statuses/get-statuses')]) . '?storeId={storeId}', 'saveConnectionUrl' => Url::to([ModuleHelper::addModuleNamePrefix('connection/submit')]) . '?storeId={storeId}', 'savePaymentSettingsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('general-settings/save-payment-settings')]) . '?storeId={storeId}', 'saveLogSettingsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('general-settings/save-log-settings')]) . '?storeId={storeId}', 'savePayByLinkSettingsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('general-settings/save-paybylink-settings')]) . '?storeId={storeId}', 'webhooksUrl' => tep_catalog_href_link(ModuleHelper::addModuleNamePrefix('webhooks/process')) . '?storeId={storeId}', 'disconnectUrl' => Url::to([ModuleHelper::addModuleNamePrefix('general-settings/disconnect')]) . '?storeId={storeId}', 'getRestrictionsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('restrictions/get-restrictions')]), 'saveRestrictionsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('general-settings/save-restrictions')]) . '?storeId={storeId}'], 'monitoring' => ['getMonitoringLogsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('monitoring/get-monitoring-logs')]) . '?storeId={storeId}', 'getWebhookLogsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('monitoring/get-webhook-logs')]) . '?storeId={storeId}', 'downloadMonitoringLogsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('monitoring/download-monitoring-logs')]) . '?storeId={storeId}', 'downloadWebhookLogsUrl' => Url::to([ModuleHelper::addModuleNamePrefix('monitoring/download-webhook-logs')]) . '?storeId={storeId}']];
    }
    private function getTranslations(): array
    {
        return ['default' => $this->getDefaultTranslations(), 'current' => $this->getCurrentTranslations()];
    }
    private function getDefaultTranslations(): array
    {
        $baseDir = __DIR__ . '/../assets/admin-ui/lang/';
        $coreTranslations = json_decode(file_get_contents($baseDir . 'en.json'), \true);
        $moduleTranslations = json_decode(file_get_contents($baseDir . 'integration/en.json'), \true);
        return array_merge_recursive($coreTranslations, $moduleTranslations);
    }
    private function getCurrentTranslations(): array
    {
        $baseDir = __DIR__ . '/../assets/admin-ui/lang/';
        $locale = \Yii::$app->language;
        $lang = substr($locale, 0, 2);
        $filePath = $baseDir . $lang . '.json';
        if (!file_exists($filePath)) {
            return $this->getDefaultTranslations();
        }
        $coreTranslations = json_decode(file_get_contents($filePath), \true);
        $moduleTranslations = json_decode(file_get_contents($baseDir . 'integration/' . $lang . '.json'), \true);
        return array_merge_recursive($coreTranslations, $moduleTranslations);
    }
}
