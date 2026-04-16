<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\classes\modules\Module;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
class Encryptor implements \common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Encryption\Encryptor
{
    /**
     * @inheritDoc
     */
    public function encrypt(string $data): string
    {
        $key = $this->getEncryptionKey();
        if (empty($key)) {
            return $data;
        }
        return utf8_encode(\Yii::$app->getSecurity()->encryptByKey($data, $key));
    }
    /**
     * @inheritDoc
     */
    public function decrypt(string $encryptedData): string
    {
        $key = $this->getEncryptionKey();
        if (empty($key)) {
            return $encryptedData;
        }
        return \Yii::$app->getSecurity()->decryptByKey(utf8_decode($encryptedData), $key);
    }
    private function getEncryptionKey(): string
    {
        $moduleCode = ModuleHelper::getModuleConfig()->getModuleName();
        $moduleClass = Module::getModule($moduleCode, 'payment');
        if (!$moduleClass) {
            return '';
        }
        $module = new $moduleClass();
        $key = $module->getEncryptionKey();
        if (empty($key)) {
            $key = \Yii::$app->params['secKey.backend'];
        }
        return $key;
    }
}
