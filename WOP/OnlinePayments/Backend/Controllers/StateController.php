<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use yii\web\Response;
/**
 * Class StateController
 *
 * @package OnlinePayments\Backend\Controllers
 */
class StateController extends ModulesController
{
    public function actionGetState(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $result = AdminAPI::get()->integration($storeId)->getState();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
}
