<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use yii\web\Response;
/**
 * Class VersionController
 *
 * @package OnlinePayments\Backend\Controllers
 */
class VersionController extends ModulesController
{
    public function actionGetVersion(): array
    {
        $result = AdminAPI::get()->version()->getVersionInfo();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
}
