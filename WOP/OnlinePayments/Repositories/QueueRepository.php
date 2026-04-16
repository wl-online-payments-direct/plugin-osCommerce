<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Entity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\Operators;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Utility\IndexHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\TaskExecution\QueueItem;
use common\modules\orderPayment\WOP\OnlinePayments\Models\QueueEntity;
/**
 * Queue Repository.
 *
 * Example of a specialized repository for queue items.
 * This demonstrates how to extend BaseRepository with a specific model.
 *
 * @package OnlinePayments\Repositories
 */
class QueueRepository extends BaseRepository implements QueueItemRepository
{
    const THIS_CLASS_NAME = __CLASS__;
    /**
     * QueueRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(QueueEntity::class);
    }
    /**
     * {@inheritdoc}
     */
    public static function getClassName(): string
    {
        return static::THIS_CLASS_NAME;
    }
    /**
     * {@inheritdoc}
     */
    protected function getDefaultModelClass(): string
    {
        return QueueEntity::class;
    }
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
    public function findOldestQueuedItems(int $priority, int $limit = 10): array
    {
        $queuedItems = [];
        try {
            $runningQueueNames = $this->getRunningQueueNames();
            $queuedItems = $this->getQueuedItems($priority, $runningQueueNames, $limit);
        } catch (\Exception $e) {
            // In case of database exception return empty result set.
        }
        return $queuedItems;
    }
    /**
     * @param QueueItem $queueItem
     * @param array $additionalWhere
     *
     * @return int
     *
     * @throws QueryFilterInvalidParamException
     * @throws QueueItemSaveException
     */
    public function saveWithCondition(QueueItem $queueItem, array $additionalWhere = []): int
    {
        if ($queueItem->getId()) {
            $this->updateQueueItem($queueItem, $additionalWhere);
            return $queueItem->getId();
        }
        return $this->save($queueItem);
    }
    public function batchStatusUpdate(array $ids, string $status)
    {
        $statusIndex = $this->getIndexMapping('status');
        $modelClass = $this->modelClass;
        $modelClass::updateAll([$statusIndex => $status], ['id' => $ids]);
    }
    /**
     * @param QueueItem $queueItem
     * @param array $additionalWhere
     *
     * @return void
     *
     * @throws QueueItemSaveException
     * @throws QueryFilterInvalidParamException
     */
    protected function updateQueueItem(QueueItem $queueItem, array $additionalWhere): void
    {
        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $queueItem->getId());
        foreach ($additionalWhere as $name => $value) {
            if ($value === null) {
                $filter->where($name, Operators::NULL);
                continue;
            }
            $filter->where($name, Operators::EQUALS, $value);
        }
        /** @var QueueItem $item */
        $item = $this->selectOne($filter);
        if ($item === null) {
            throw new QueueItemSaveException("Cannot update queue item with id {$queueItem->getId()}.");
        }
        $this->update($queueItem);
    }
    /**
     * @return array
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getRunningQueueNames(): array
    {
        $filter = new QueryFilter();
        $filter->where('status', Operators::EQUALS, QueueItem::IN_PROGRESS);
        $filter->setLimit(10000);
        /** @var QueueItem[] $runningQueueItems */
        $runningQueueItems = $this->select($filter);
        return array_map(function (QueueItem $runningQueueItem) {
            return $runningQueueItem->getQueueName();
        }, $runningQueueItems);
    }
    protected function getQueuedItems(int $priority, array $runningQueueNames, int $limit): array
    {
        $queuedItems = [];
        $queueNameIndex = $this->getIndexMapping('queueName');
        $statusIndex = $this->getIndexMapping('status');
        $priorityIndex = $this->getIndexMapping('priority');
        try {
            $modelClass = $this->modelClass;
            // Build the subquery to get the minimum ID for each queue name
            $subQuery = $modelClass::find()->select([$queueNameIndex, 'MIN(id) AS id'])->where(['entity_type' => 'QueueItem', $statusIndex => QueueItem::QUEUED, $priorityIndex => $priority])->groupBy($queueNameIndex)->limit($limit);
            // Add NOT IN condition for running queue names
            if (!empty($runningQueueNames)) {
                $subQuery->andWhere(['NOT IN', $queueNameIndex, $runningQueueNames]);
            }
            // Build the main query with a join
            $mainQuery = $modelClass::find()->select(['queueTable.id', 'queueTable.data'])->from(['queueTable' => $modelClass::tableName()])->innerJoin(['queueView' => $subQuery], 'queueView.id = queueTable.id');
            $records = $mainQuery->asArray()->all();
            $queuedItems = $this->translateToEntities($records);
        } catch (\Exception $e) {
            // In case of exception return empty result set
        }
        return $queuedItems;
    }
    protected function getIndexMapping(string $property): ?string
    {
        $this->indexMapping = IndexHelper::mapFieldsToIndexes(new $this->entityClass());
        if (array_key_exists($property, $this->indexMapping)) {
            return 'index_' . $this->indexMapping[$property];
        }
        return null;
    }
}
