<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\PaymentLink;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\Operators;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use common\modules\orderPayment\WOP\OnlinePayments\Entities\PayByLinkHash;
use common\modules\orderPayment\WOP\OnlinePayments\Repositories\BaseRepositoryWithConditionalDelete;
use common\modules\orderPayment\WOP\OnlinePayments\Services\Integration\Encryptor;
class PayByLinkHashService
{
    private BaseRepositoryWithConditionalDelete $repository;
    private Encryptor $encryptor;
    /**
     * @param BaseRepositoryWithConditionalDelete $repository
     * @param Encryptor $encryptor
     */
    public function __construct(BaseRepositoryWithConditionalDelete $repository, Encryptor $encryptor)
    {
        $this->repository = $repository;
        $this->encryptor = $encryptor;
    }
    public function save(PayByLinkHash $payByLinkHash)
    {
        $payByLinkHash->setHash($this->encryptor->encrypt($payByLinkHash->getHash()));
        $this->repository->save($payByLinkHash);
    }
    public function get(string $orderId): ?PayByLinkHash
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('orderId', Operators::EQUALS, $orderId);
        $payByLinkHash = $this->repository->selectOne($queryFilter);
        if ($payByLinkHash) {
            $payByLinkHash->setHash($this->encryptor->decrypt($payByLinkHash->getHash()));
        }
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $payByLinkHash;
    }
    public function delete(string $orderId): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('orderId', Operators::EQUALS, $orderId);
        $this->repository->deleteWhere($queryFilter);
    }
}
