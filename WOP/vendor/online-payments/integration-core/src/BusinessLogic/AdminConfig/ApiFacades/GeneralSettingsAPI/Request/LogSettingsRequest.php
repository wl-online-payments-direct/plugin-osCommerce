<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\GeneralSettingsAPI\Request;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\ApiFacades\Request\Request;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidLogRecordsLifetimeException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\LogRecordsLifetime;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\LogSettings;
/**
 * Class LogSettingsRequest
 *
 * @package OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\GeneralSettingsAPI\Request
 */
class LogSettingsRequest extends Request
{
    protected bool $debugMode;
    protected int $days;
    /**
     * @param bool $debugMode
     * @param int $days
     */
    public function __construct(bool $debugMode, int $days)
    {
        $this->debugMode = $debugMode;
        $this->days = $days;
    }
    /**
     * @inheritDoc
     *
     * @throws InvalidLogRecordsLifetimeException
     */
    public function transformToDomainModel(): object
    {
        return new LogSettings($this->debugMode, LogRecordsLifetime::create($this->days));
    }
}
