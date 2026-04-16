<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ConnectionAPI\Request\ConnectionRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Services\ModuleVisibilityService;
use yii\web\Response;
class ConnectionController extends ModulesController
{
    public function actionGetConnection(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $result = AdminAPI::get()->connection($storeId)->getConnectionConfig();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
    public function actionSubmit(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $data = json_decode(\Yii::$app->request->getRawBody(), \true);
        $connectionRequest = new ConnectionRequest($data['mode'] ?? '', $data['testData']['pspid'] ?? '', $data['testData']['apiKey'] ?? '', $data['testData']['apiSecret'] ?? '', $data['testData']['webhooksKey'] ?? '', $data['testData']['webhooksSecret'] ?? '', $data['liveData']['pspid'] ?? '', $data['liveData']['apiKey'] ?? '', $data['liveData']['apiSecret'] ?? '', $data['liveData']['webhooksKey'] ?? '', $data['liveData']['webhooksSecret'] ?? '');
        $result = AdminAPI::get()->connection($storeId)->connect($connectionRequest);
        if ($result->isSuccessful()) {
            /** @var ModuleVisibilityService $service */
            $service = ServiceRegister::getService(ModuleVisibilityService::class);
            StoreContext::doWithStore($storeId, function () use ($service) {
                $service->setModuleVisibility();
            });
        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
}
