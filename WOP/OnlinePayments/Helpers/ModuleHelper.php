<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Helpers;

use common\helpers\OrderPayment as OrderPaymentHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\StatusCode;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Services\ModuleConfig;
/**
 * Class ModuleHelper.
 *
 * @package OnlinePayments\Helpers
 */
class ModuleHelper
{
    public static function getModuleConfig(): ModuleConfig
    {
        /** @var ModuleConfig $moduleConfig */
        $moduleConfig = ServiceRegister::getService(ModuleConfig::class);
        return $moduleConfig;
    }
    public static function addModuleNamePrefix(string $subject, string $separator = '-'): string
    {
        return self::getModuleConfig()->getModuleName() . $separator . $subject;
    }
    public static function removeModuleNamePrefix(string $subject, string $separator = '-'): string
    {
        return str_replace(self::getModuleConfig()->getModuleName() . $separator, '', $subject);
    }
    public static function getFullConstantName(string $shortName): string
    {
        return 'MODULE_PAYMENT_' . strtoupper(ModuleHelper::addModuleNamePrefix($shortName, '_'));
    }
    public static function getConstantValue(string $shortName): string
    {
        return constant(self::getFullConstantName($shortName));
    }
    public static function getFullTableName(string $shortName): string
    {
        return strtolower(self::getModuleConfig()->getBrand()) . '_' . $shortName;
    }
    /**
     * Returns a full module asset file admin URL
     *
     * @param string $assetFile Relative to the folder of Bootstrap.php file (module library root folder)
     * @return string asset file admin URL
     */
    public static function getAdminAssetUrl(string $assetFile): string
    {
        $baseAdminModuleUrl = '@web/../lib/common/modules/orderPayment/' . self::getModuleConfig()->getBrand();
        return $baseAdminModuleUrl . '/' . ltrim($assetFile, '/');
    }
    public static function getPaymentTransactionStatus(StatusCode $statusCode): int
    {
        if ($statusCode->equals(StatusCode::completed())) {
            return OrderPaymentHelper::OPYS_SUCCESSFUL;
        }
        if ($statusCode->equals(StatusCode::authorized())) {
            return OrderPaymentHelper::OPYS_PENDING;
        }
        if ($statusCode->isCanceledOrRejected()) {
            return OrderPaymentHelper::OPYS_REFUSED;
        }
        if ($statusCode->isRefunded()) {
            return OrderPaymentHelper::OPYS_REFUNDED;
        }
        return OrderPaymentHelper::OPYS_PROCESSING;
    }
}
