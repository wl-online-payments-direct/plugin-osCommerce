<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\AdminConfig\Proxies;

use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Sdk\MerchantClientFactory;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Connection\Proxies\ConnectionProxyInterface as BaseConnectionProxy;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\ConnectionDetails;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidConnectionDetailsException;
class ConnectionProxy implements BaseConnectionProxy
{
    private const CONNECTION_VALID = 'OK';
    private MerchantClientFactory $clientFactory;
    public function __construct(MerchantClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    /**
     * @inheritDoc
     *
     * @throws InvalidConnectionDetailsException
     */
    public function isConnectionValid(ConnectionDetails $connectionDetails): bool
    {
        return $this->clientFactory->get($connectionDetails)->services()->testConnection()->getResult() === self::CONNECTION_VALID;
    }
}
