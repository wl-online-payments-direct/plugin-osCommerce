<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\PaymentSettings;
/**
 * Interface PaymentSettingsRepositoryInterface.
 *
 * @package OnlinePayments\Core\BusinessLogic\Domain\Payment\Repositories
 */
interface PaymentSettingsRepositoryInterface
{
    public function getPaymentSettings(): ?PaymentSettings;
}
