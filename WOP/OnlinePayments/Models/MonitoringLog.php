<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Models;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Class MonitoringLog
 *
 * @package OnlinePayments\Models
 */
class MonitoringLog extends BaseEntity
{
    public static function tableName(): string
    {
        return ModuleHelper::getFullTableName('monitoring_logs');
    }
}
