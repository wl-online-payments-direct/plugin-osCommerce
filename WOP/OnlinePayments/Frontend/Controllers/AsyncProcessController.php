<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Frontend\Controllers;

use frontend\controllers\Sceleton;
use common\modules\orderPayment\WOP\OnlinePayments\Common\Controllers\ControllerViewPathResolver;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use yii\web\Response;
/**
 * Class AsyncProcessController
 *
 * Handles asynchronous process execution for background tasks.
 *
 * @package OnlinePayments\Frontend\Controllers
 */
class AsyncProcessController extends Sceleton
{
    use ControllerViewPathResolver;
    /**
     * Executes an async process identified by the guid parameter.
     *
     * @return array
     */
    public function actionIndex(): array
    {
        $this->respondOK();
        $guid = trim(\Yii::$app->request->get('guid', ''));
        if ($guid !== 'auto-configure') {
            /** @var AsyncProcessService $asyncProcessService */
            $asyncProcessService = ServiceRegister::getService(AsyncProcessService::CLASS_NAME);
            $asyncProcessService->runProcess($guid);
        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return ['success' => \true];
    }
    /**
     * Sends HTTP response immediately and continues processing in the background.
     *
     * This method uses various techniques to close the connection with the client
     * while allowing the script to continue running server-side.
     *
     * @return void
     */
    protected function respondOK(): void
    {
        // check if fastcgi_finish_request is callable
        if (function_exists('fastcgi_finish_request') && is_callable('fastcgi_finish_request')) {
            /*
             * This works in Nginx but the next approach not
             */
            fastcgi_finish_request();
            return;
        }
        if (function_exists('common\modules\orderPayment\WOP\litespeed_finish_request') && is_callable('common\modules\orderPayment\WOP\litespeed_finish_request')) {
            litespeed_finish_request();
            return;
        }
        ignore_user_abort(\true);
        ob_start();
        header('HTTP/1.1 204 No Content');
        header('Content-Encoding: none');
        header('Content-Length: 0');
        header('Connection: close');
        ob_end_flush();
        ob_flush();
        flush();
    }
}
