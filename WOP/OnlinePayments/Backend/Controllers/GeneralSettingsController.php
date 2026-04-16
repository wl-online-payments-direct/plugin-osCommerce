<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\GeneralSettingsAPI\Request\LogSettingsRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\GeneralSettingsAPI\Request\PayByLinkSettingsRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\GeneralSettingsAPI\Request\PaymentSettingsRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\ActiveConnectionProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Integration\ImageHandler;
use common\modules\orderPayment\WOP\OnlinePayments\Services\ModuleRestrictionsService;
use yii\web\Response;
class GeneralSettingsController extends ModulesController
{
    public function actionGetSettings(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $result = AdminAPI::get()->generalSettings($storeId)->getGeneralSettings();
        /** @var ModuleRestrictionsService $service */
        $service = ServiceRegister::getService(ModuleRestrictionsService::class);
        $resultArray = $result->toArray();
        $resultArray['restrictions'] = $service->getAllSelectedOptions($storeId);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $resultArray;
    }
    public function actionSavePaymentSettings(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $data = json_decode(\Yii::$app->request->getRawBody(), \true);
        $result = AdminAPI::get()->generalSettings($storeId)->savePaymentSettings(new PaymentSettingsRequest($data['paymentAction'] ?? null, $data['automaticCapture'] ?? null, $data['numberOfPaymentAttempts'] ?? null, $data['applySurcharge'] ?? null, $data['paymentCapturedStatus'] ?? '', $data['paymentErrorStatus'] ?? '', $data['paymentPendingStatus'] ?? '', $data['paymentAuthorizedStatus'] ?? '', $data['paymentCancelledStatus'] ?? '', $data['paymentRefundedStatus'] ?? '', $data['template'] ?? '', $data['paymentPartiallyRefundedStatus'] ?? ''));
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
    public function actionSaveRestrictions(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $data = json_decode(\Yii::$app->request->getRawBody(), \true);
        /** @var ModuleRestrictionsService $service */
        $service = ServiceRegister::getService(ModuleRestrictionsService::class);
        $result = $service->saveAllSelectedOptions($storeId, $data);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;
    }
    public function actionSaveLogSettings(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $data = json_decode(\Yii::$app->request->getRawBody(), \true);
        $result = AdminAPI::get()->generalSettings($storeId)->saveLogSettings(new LogSettingsRequest($data['debugMode'] ?? null, $data['logDays'] ?? null));
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
    public function actionSavePaybylinkSettings(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $data = json_decode(\Yii::$app->request->getRawBody(), \true);
        $result = AdminAPI::get()->generalSettings($storeId)->savePayByLinkSettings(new PayByLinkSettingsRequest($data['enabled'] ?? null, $data['title'] ?? '', $data['expirationTime'] ?? 7));
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
    public function actionDisconnect(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        /** @var ActiveConnectionProvider $activeConnectionProvider */
        $activeConnectionProvider = ServiceRegister::getService(ActiveConnectionProvider::class);
        $mode = StoreContext::doWithStore($storeId, function () use ($activeConnectionProvider) {
            return (string) $activeConnectionProvider->get()->getMode();
        });
        ImageHandler::removeDirectoryForStore($storeId, $mode);
        /** @var ModuleRestrictionsService $restrictionsService */
        $restrictionsService = ServiceRegister::getService(ModuleRestrictionsService::class);
        $restrictionsService->resetAllRestrictions($storeId);
        $result = AdminAPI::get()->generalSettings($storeId)->disconnect();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
}
