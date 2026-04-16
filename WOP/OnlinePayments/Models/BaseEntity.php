<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Models;

use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use yii\db\ActiveRecord;
/**
 * Base Entity ActiveRecord model.
 *
 * This class provides a base ActiveRecord implementation for the generic entity storage structure.
 * Extend this class for specific entity types to add custom behavior, relations, or validation.
 *
 * @property int $id
 * @property string|null $entity_type
 * @property string|null $index_1
 * @property string|null $index_2
 * @property string|null $index_3
 * @property string|null $index_4
 * @property string|null $index_5
 * @property string|null $index_6
 * @property string|null $index_7
 * @property string|null $index_8
 * @property string|null $index_9
 * @property string|null $data JSON encoded entity data
 *
 * @package OnlinePayments\Models
 */
class BaseEntity extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return ModuleHelper::getFullTableName('entity');
    }
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [[['entity_type'], 'string', 'max' => 255], [['index_1', 'index_2', 'index_3', 'index_4', 'index_5', 'index_6', 'index_7', 'index_8', 'index_9'], 'string', 'max' => 191], [['data'], 'string']];
    }
}
