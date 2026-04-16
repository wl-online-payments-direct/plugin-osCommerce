<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Payment\StatusCode;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Time\TimeProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\Operators;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
/**
 * Class AuthorizedTransactionsRepository.
 *
 * @package OnlinePayments\Core\Bootstrap\DataAccess\PaymentTransaction
 */
class AuthorizedTransactionsRepository
{
    private RepositoryInterface $repository;
    private TimeProviderInterface $timeProvider;
    /**
     * @param RepositoryInterface $repository
     * @param TimeProviderInterface $timeProvider
     */
    public function __construct(RepositoryInterface $repository, TimeProviderInterface $timeProvider)
    {
        $this->repository = $repository;
        $this->timeProvider = $timeProvider;
    }
    public function get(int $limit = 10): array
    {
        $queryFilter = new QueryFilter();
        $now = $this->timeProvider->getCurrentLocalTime();
        $queryFilter->where('statusCode', Operators::EQUALS, StatusCode::authorized()->getCode())->where('captureAtTimestamp', Operators::NOT_EQUALS, 0)->where('captureAtTimestamp', Operators::LESS_OR_EQUAL_THAN, $now->getTimestamp())->orderBy('captureAtTimestamp', QueryFilter::ORDER_DESC)->setLimit($limit);
        /** @var PaymentTransactionEntity[] $entities */
        $entities = $this->repository->select($queryFilter);
        return $entities;
    }
}
