<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Entity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
/**
 * Base Repository with Conditional Delete.
 *
 * Extends BaseRepository to add the deleteWhere capability required by
 * the ConditionallyDeletes interface.
 *
 * @package OnlinePayments\Repositories
 */
class BaseRepositoryWithConditionalDelete extends BaseRepository implements ConditionallyDeletes
{
    const THIS_CLASS_NAME = __CLASS__;
    /**
     * {@inheritdoc}
     */
    public static function getClassName(): string
    {
        return static::THIS_CLASS_NAME;
    }
    /**
     * Deletes entities matching the given filter.
     *
     * This method performs a direct database DELETE query for better performance
     * compared to selecting entities and deleting them one by one.
     *
     * @param QueryFilter|null $queryFilter Filter to match entities for deletion.
     *
     * @return int Number of deleted records.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteWhere(QueryFilter $queryFilter = null): int
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass();
        $type = $entity->getConfig()->getType();
        $modelClass = $this->modelClass;
        $conditions = ['entity_type' => $type];
        if ($queryFilter) {
            $conditions = $this->buildWhereConditions($queryFilter, $entity);
            $conditions = ['and', ['entity_type' => $type], $conditions];
        }
        $deletedCount = $modelClass::deleteAll($conditions);
        return (int) $deletedCount;
    }
}
