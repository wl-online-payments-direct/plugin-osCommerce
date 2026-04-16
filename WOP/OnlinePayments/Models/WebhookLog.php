<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Models;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Class WebhookLog
 *
 * @package OnlinePayments\Repositories
 */
class WebhookLog extends BaseEntity
{
    public static function tableName(): string
    {
        return ModuleHelper::getFullTableName('webhook_logs');
    }
}
