<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Backend\Controllers;

use backend\controllers\ModulesController;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\AdminAPI\AdminAPI;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Request\PaymentMethodRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\ActiveConnectionProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Exceptions\ImageUploadException;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Integration\ImageHandler;
use yii\web\Response;
use yii\web\UploadedFile;
class PaymentsController extends ModulesController
{
    public function actionGetList(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $result = AdminAPI::get()->payment($storeId)->list();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
    public function actionEnable(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $data = json_decode(\Yii::$app->request->getRawBody(), \true);
        $result = AdminAPI::get()->payment($storeId)->enable($data['paymentProductId'], $data['enabled']);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
    public function actionSaveMethod(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $data = \Yii::$app->request->post();
        $result = AdminAPI::get()->payment($storeId)->save($this->createPaymentMethodRequest($storeId, $data));
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
    public function actionGet(): array
    {
        $storeId = \Yii::$app->request->get('storeId');
        $methodId = \Yii::$app->request->get('methodId');
        $result = AdminAPI::get()->payment($storeId)->getPaymentMethod($methodId);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $result->toArray();
    }
    private function createPaymentMethodRequest(string $storeId, array $requestData): PaymentMethodRequest
    {
        /** @var ActiveConnectionProvider $activeConnectionProvider */
        $activeConnectionProvider = ServiceRegister::getService(ActiveConnectionProvider::class);
        $mode = StoreContext::doWithStore($storeId, function () use ($activeConnectionProvider) {
            return (string) $activeConnectionProvider->get()->getMode();
        });
        $fileData = $_FILES['logo'] ?? null;
        if ($fileData) {
            $file = new UploadedFile(['name' => $fileData['name'], 'tempName' => $fileData['tmp_name'], 'type' => $fileData['type'], 'size' => $fileData['size'], 'error' => $fileData['error']]);
        }
        if ($file && !ImageHandler::saveImage($file, $requestData['paymentProductId'], $storeId, $mode)) {
            throw new ImageUploadException('Failed to upload image.');
        }
        $translations = json_decode($requestData['name'], \true);
        $names = [];
        foreach ($translations as $translation) {
            $names[$translation['locale']] = $translation['value'];
        }
        $additionalData = [];
        $titles = [];
        $enableGroupCards = null;
        $instantPayment = null;
        $recurrenceType = null;
        $signatureType = null;
        $paymentProductId = null;
        $sessionTimeout = null;
        $enable3ds = null;
        $enforceStrongAuthentication = null;
        $enable3dsExemption = null;
        $exemptionType = null;
        $exemptionLimit = null;
        $flowType = null;
        if (isset($requestData['additionalData'])) {
            $additionalData = json_decode($requestData['additionalData'], \true);
            $instantPayment = $additionalData['instantPayment'] ?? null;
            $recurrenceType = $additionalData['recurrenceType'] ?? null;
            $signatureType = $additionalData['signatureType'] ?? null;
            $paymentProductId = $additionalData['paymentProductId'] ?? null;
            $sessionTimeout = $additionalData['sessionTimeout'] ?? null;
            $enableGroupCards = $additionalData['enableGroupCards'] ?? null;
            $enable3ds = $additionalData['enable3ds'] ?? null;
            $enforceStrongAuthentication = $additionalData['enforceStrongAuthentication'] ?? null;
            $enable3dsExemption = $additionalData['enable3dsExemption'] ?? null;
            $exemptionType = $additionalData['exemptionType'] ?? null;
            $exemptionLimit = $additionalData['exemptionLimit'] ?? null;
            $flowType = $additionalData['flowType'] ?? null;
            $vaultTitles = $additionalData['vaultTitleCollection'] ?? [];
            $titles = [];
            foreach ($vaultTitles as $vaultTitle) {
                $titles[$vaultTitle['locale']] = $vaultTitle['value'];
            }
        }
        $logo = '';
        if ($file) {
            $logo = ImageHandler::getImageUrl($requestData['paymentProductId'], $storeId, $mode);
        }
        if (!$logo && isset($additionalData['logo'])) {
            $logo = $additionalData['logo'];
        }
        return new PaymentMethodRequest($requestData['paymentProductId'], $names, filter_var($requestData['enabled'], \FILTER_VALIDATE_BOOLEAN), $requestData['template'] ?? '', $requestData['paymentAction'] ?? null, $titles, $logo, $enableGroupCards, null, $sessionTimeout, $paymentProductId, $recurrenceType, $signatureType, $instantPayment, $enable3ds, $enforceStrongAuthentication, $enable3dsExemption, $exemptionType, $exemptionLimit, $flowType);
    }
}
