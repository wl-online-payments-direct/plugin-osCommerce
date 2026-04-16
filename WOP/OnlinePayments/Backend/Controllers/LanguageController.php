<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use yii\web\Response;
class LanguageController extends ModulesController
{
    public function actionGetLanguages(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $result = AdminAPI::get()->language($storeId)->getLanguages();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
}
