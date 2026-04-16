<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Frontend\Controllers;

use common\classes\platform;
use frontend\controllers\Sceleton;
use common\modules\orderPayment\WOP\OnlinePayments\Common\Controllers\ControllerViewPathResolver;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\WebhookAPI\WebhookAPI;
use yii\web\Response;
class WebhooksController extends Sceleton
{
    use ControllerViewPathResolver;
    public $enableCsrfValidation = \false;
    public function actionProcess(): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        WebhookAPI::get()->webhooks(platform::currentId())->process(\Yii::$app->request->getRawBody(), array_map(static function ($headerValues) {
            return implode(', ', $headerValues);
        }, \Yii::$app->request->getHeaders()->toArray()));
        return [];
    }
}
