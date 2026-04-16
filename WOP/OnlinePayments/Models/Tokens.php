<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Models;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Class Tokens.
 *
 * @package OnlinePayments\Models
 */
class Tokens extends BaseEntity
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return ModuleHelper::getFullTableName('tokens');
    }
}
