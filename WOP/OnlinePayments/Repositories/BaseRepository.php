<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Entity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\IntermediateObject;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\QueryCondition;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Utility\EntityTranslator;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Utility\IndexHelper;
use yii\db\ActiveQuery;
/**
 * Base Repository implementation.
 *
 * This class implements the RepositoryInterface from the OnlinePayments SDK
 * and provides database persistence using Yii's ActiveRecord pattern.
 *
 * @package OnlinePayments\Repositories
 */
class BaseRepository implements RepositoryInterface
{
    const THIS_CLASS_NAME = __CLASS__;
    /**
     * @var string The SDK Entity class this repository manages.
     */
    protected string $entityClass;
    /**
     * @var string The ActiveRecord model class to use for database operations.
     */
    protected string $modelClass;
    /**
     * BaseRepository constructor.
     *
     * @param string|null $modelClass Optional ActiveRecord model class.
     *                                If not provided, will be determined from entity configuration.
     */
    public function __construct(?string $modelClass = null)
    {
        if ($modelClass !== null) {
            $this->modelClass = $modelClass;
        }
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
    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
        // If model class not explicitly set, determine it from entity
        if (!isset($this->modelClass)) {
            $this->modelClass = $this->getDefaultModelClass();
        }
    }
    /**
     * {@inheritdoc}
     */
    public function select(QueryFilter $filter = null): array
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass();
        $query = $this->createQuery($entity);
        if ($filter) {
            $this->applyFilter($query, $filter, $entity);
        }
        $results = $query->asArray()->all();
        return $this->translateToEntities($results);
    }
    /**
     * {@inheritdoc}
     */
    public function selectOne(QueryFilter $filter = null): ?Entity
    {
        if ($filter === null) {
            $filter = new QueryFilter();
        }
        $filter->setLimit(1);
        $results = $this->select($filter);
        return empty($results) ? null : $results[0];
    }
    /**
     * {@inheritdoc}
     */
    public function save(Entity $entity): int
    {
        $modelClass = $this->modelClass;
        $model = new $modelClass();
        $this->populateModel($model, $entity);
        if (!$model->save()) {
            throw new \RuntimeException('Failed to save entity: ' . json_encode($model->errors));
        }
        $id = (int) $model->id;
        $entity->setId($id);
        return $id;
    }
    /**
     * {@inheritdoc}
     */
    public function update(Entity $entity, QueryFilter $queryFilter = null): bool
    {
        /** @var Entity $emptyEntity */
        $emptyEntity = new $this->entityClass();
        $query = $this->createQuery($emptyEntity);
        $query->andWhere(['id' => $entity->getId()]);
        if ($queryFilter) {
            $this->applyFilter($query, $queryFilter, $emptyEntity);
        }
        $model = $query->one();
        if ($model === null) {
            return \false;
        }
        $this->populateModel($model, $entity);
        return $model->save();
    }
    /**
     * {@inheritdoc}
     */
    public function delete(Entity $entity): bool
    {
        if ($entity->getId() === null) {
            return \false;
        }
        $modelClass = $this->modelClass;
        $model = $modelClass::findOne($entity->getId());
        if ($model === null) {
            return \false;
        }
        return $model->delete() !== \false;
    }
    /**
     * {@inheritdoc}
     */
    public function count(QueryFilter $filter = null): int
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass();
        $query = $this->createQuery($entity);
        if ($filter) {
            $this->applyFilter($query, $filter, $entity);
        }
        return (int) $query->count();
    }
    /**
     * Creates a base query for the entity type.
     *
     * @param Entity $entity
     * @return ActiveQuery
     */
    protected function createQuery(Entity $entity): ActiveQuery
    {
        $modelClass = $this->modelClass;
        $type = $entity->getConfig()->getType();
        return $modelClass::find()->where(['entity_type' => $type]);
    }
    /**
     * Applies QueryFilter to ActiveQuery.
     *
     * @param ActiveQuery $query
     * @param QueryFilter $filter
     * @param Entity $entity
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function applyFilter(ActiveQuery $query, QueryFilter $filter, Entity $entity): void
    {
        $fieldIndexMap = IndexHelper::mapFieldsToIndexes($entity);
        if ($filter->getConditions()) {
            $whereConditions = $this->buildWhereConditions($filter, $entity);
            $query->andWhere($whereConditions);
        }
        if ($filter->getOrderByColumn()) {
            $column = $filter->getOrderByColumn();
            $direction = $filter->getOrderDirection() === 'ASC' ? \SORT_ASC : \SORT_DESC;
            if ($column !== 'id' && !array_key_exists($column, $fieldIndexMap)) {
                throw new QueryFilterInvalidParamException('Unknown or not indexed OrderBy column ' . $filter->getOrderByColumn());
            }
            $orderByColumn = $column === 'id' ? 'id' : 'index_' . $fieldIndexMap[$column];
            $query->orderBy([$orderByColumn => $direction]);
        }
        if ($filter->getLimit()) {
            $query->limit($filter->getLimit());
        }
        if ($filter->getOffset()) {
            $query->offset($filter->getOffset());
        }
    }
    /**
     * Builds WHERE conditions from QueryFilter.
     *
     * @param QueryFilter $filter
     * @param Entity $entity
     *
     * @return array
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function buildWhereConditions(QueryFilter $filter, Entity $entity): array
    {
        $fieldIndexMap = IndexHelper::mapFieldsToIndexes($entity);
        $fieldIndexMap['id'] = 0;
        $orGroups = [];
        $currentGroup = [];
        foreach ($filter->getConditions() as $condition) {
            $column = $condition->getColumn();
            if (!array_key_exists($column, $fieldIndexMap)) {
                throw new QueryFilterInvalidParamException('Field ' . $column . ' is not indexed in class ' . $this->entityClass);
            }
            $columnName = $column === 'id' ? 'id' : 'index_' . $fieldIndexMap[$column];
            $value = $this->prepareValue($condition);
            $currentGroup[] = $this->buildCondition($columnName, $condition->getOperator(), $value);
            if ($condition->getChainOperator() === 'OR') {
                $orGroups[] = ['and', ...$currentGroup];
                $currentGroup = [];
            }
        }
        if (!empty($currentGroup)) {
            $orGroups[] = ['and', ...$currentGroup];
        }
        if (count($orGroups) === 1) {
            return $orGroups[0];
        }
        return ['or', ...$orGroups];
    }
    /**
     * Builds a single query condition.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     *
     * @return array
     */
    protected function buildCondition(string $column, string $operator, $value): array
    {
        switch ($operator) {
            case '!=':
                return ['!=', $column, $value];
            case '>':
                return ['>', $column, $value];
            case '>=':
                return ['>=', $column, $value];
            case '<':
                return ['<', $column, $value];
            case '<=':
                return ['<=', $column, $value];
            case 'IN':
                return ['in', $column, $value];
            case 'NOT IN':
                return ['not in', $column, $value];
            case 'IS NULL':
                return ['is', $column, null];
            case 'IS NOT NULL':
                return ['is not', $column, null];
            case 'LIKE':
                return ['like', $column, $value];
            default:
                return [$column => $value];
        }
    }
    /**
     * Prepares condition value with proper type casting.
     *
     * @param QueryCondition $condition
     *
     * @return mixed
     */
    protected function prepareValue(QueryCondition $condition)
    {
        $value = $condition->getValue();
        if ($condition->getColumn() !== 'id' && $value !== null) {
            $value = IndexHelper::castFieldValue($value, $condition->getValueType());
        }
        return $value;
    }
    /**
     * Populates ActiveRecord model from SDK Entity.
     *
     * @param \yii\db\ActiveRecord $model
     * @param Entity $entity
     */
    protected function populateModel(\yii\db\ActiveRecord $model, Entity $entity): void
    {
        $indexes = IndexHelper::transformFieldsToIndexes($entity);
        $data = $entity->toArray();
        $data['class_name'] = $entity::getClassName();
        $model->entity_type = $entity->getConfig()->getType();
        $model->data = json_encode($data);
        for ($i = 1; $i <= 9; $i++) {
            $indexField = 'index_' . $i;
            $model->{$indexField} = $indexes[$i] ?? null;
        }
    }
    /**
     * Translates database results to SDK Entity objects.
     *
     * @param array $results
     *
     * @return Entity[]
     *
     * @throws EntityClassException
     */
    protected function translateToEntities(array $results): array
    {
        $translator = new EntityTranslator();
        $translator->init($this->entityClass);
        $intermediates = [];
        foreach ($results as $item) {
            $obj = new IntermediateObject();
            $unserializedData = json_decode($item['data'], \true);
            if (!$unserializedData['id']) {
                $unserializedData['id'] = (int) $item['id'];
            }
            $obj->setData(json_encode($unserializedData));
            for ($i = 1; $i <= 9; $i++) {
                $obj->setIndexValue($i, $item['index_' . $i] ?? null);
            }
            $intermediates[] = $obj;
        }
        return $translator->translate($intermediates);
    }
    /**
     * Gets the default model class name based on entity type.
     * Override this method to provide custom model class mapping.
     *
     * @return string
     */
    protected function getDefaultModelClass(): string
    {
        // By default, use a generic model for 'entity' table
        return \common\modules\orderPayment\WOP\OnlinePayments\Models\BaseEntity::class;
    }
}
