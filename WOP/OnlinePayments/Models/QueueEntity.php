<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Models;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Queue Entity Model.
 *
 * Example of a specialized entity model for queue items.
 * This demonstrates how to extend BaseEntity for specific use cases.
 *
 * @package OnlinePayments\Models
 */
class QueueEntity extends BaseEntity
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return ModuleHelper::getFullTableName('queue');
    }
}
