<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Repositories;

use DateTime;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\ActiveConnectionProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Monitoring\Repositories\RepositoryWithAdvancedSearchInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Entity;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\Operators;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
use common\modules\orderPayment\WOP\OnlinePayments\Models\WebhookLog;
class WebhookLogsRepository extends BaseRepositoryWithConditionalDelete implements RepositoryWithAdvancedSearchInterface
{
    const THIS_CLASS_NAME = __CLASS__;
    /**
     * {@inheritdoc}
     */
    public static function getClassName(): string
    {
        return static::THIS_CLASS_NAME;
    }
    public function getOrderIdByTempOrderId(string $tempOrderId): ?int
    {
        return $this->getOrderIdByReference($tempOrderId);
    }
    /**
     * Get logs with pagination and search
     *
     * @param int $pageNumber
     * @param int $pageSize
     * @param string $searchTerm
     * @param DateTime|null $disconnectTime
     *
     * @return array
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getLogs(int $pageNumber, int $pageSize, string $searchTerm, ?DateTime $disconnectTime = null): array
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass();
        $queryFilter = $this->getQuery($disconnectTime);
        $queryFilter->setOffset(($pageNumber - 1) * $pageSize)->setLimit($pageSize);
        $query = $this->createQuery($entity);
        $this->applyFilter($query, $queryFilter, $entity);
        if (!empty($searchTerm)) {
            $searchCondition = $this->buildSearchCondition($searchTerm);
            $query->andWhere($searchCondition);
        }
        $results = $query->asArray()->all();
        return $this->translateToEntities($results);
    }
    /**
     * Count logs with optional filters
     *
     * @param DateTime|null $disconnectTime
     * @param string $searchTerm
     *
     * @return int|null
     *
     * @throws QueryFilterInvalidParamException
     */
    public function countLogs(?DateTime $disconnectTime = null, string $searchTerm = ''): ?int
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass();
        $queryFilter = $this->getQuery($disconnectTime);
        $query = $this->createQuery($entity);
        $this->applyFilter($query, $queryFilter, $entity);
        if (!empty($searchTerm)) {
            $searchCondition = $this->buildSearchCondition($searchTerm);
            $query->andWhere($searchCondition);
        }
        return (int) $query->count();
    }
    /**
     * Build query filter for logs
     *
     * @param DateTime|null $disconnectTime
     *
     * @return QueryFilter
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getQuery(?DateTime $disconnectTime = null): QueryFilter
    {
        /** @var ActiveConnectionProvider $activeConnectionProvider */
        $activeConnectionProvider = ServiceRegister::getService(ActiveConnectionProvider::class);
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, StoreContext::getInstance()->getStoreId())->where('mode', Operators::EQUALS, (string) $activeConnectionProvider->get()->getMode())->orderBy('createdAt', 'DESC');
        if ($disconnectTime) {
            $queryFilter->where('createdAt', Operators::GREATER_THAN, $disconnectTime->getTimestamp());
        }
        return $queryFilter;
    }
    /**
     * Build search condition for Yii2 query
     *
     * @param string $searchTerm
     *
     * @return array Yii2 query condition array
     */
    protected function buildSearchCondition(string $searchTerm): array
    {
        $orderId = $this->getOrderIdByReference($searchTerm);
        if ($orderId) {
            return ['or', ['like', 'index_3', (string) $orderId], ['like', 'index_4', $searchTerm]];
        }
        return ['like', 'index_4', $searchTerm];
    }
    /**
     * Get order ID by reference (child_id from tmp_orders)
     *
     * @param string $reference
     *
     * @return int|null
     */
    protected function getOrderIdByReference(string $reference): ?int
    {
        if (empty($reference)) {
            return null;
        }
        $query = tep_db_query("SELECT orders_id\r\n             FROM tmp_orders\r\n             WHERE child_id = '" . tep_db_input($reference) . "'\r\n             LIMIT 1");
        if ($result = tep_db_fetch_array($query)) {
            return (int) $result['orders_id'];
        }
        return null;
    }
    protected function getDefaultModelClass(): string
    {
        return WebhookLog::class;
    }
}
