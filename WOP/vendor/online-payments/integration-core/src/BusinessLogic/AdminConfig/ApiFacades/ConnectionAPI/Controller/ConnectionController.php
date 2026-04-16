<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ConnectionAPI\Controller;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ConnectionAPI\Request\ConnectionRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ConnectionAPI\Response\ConnectionConfigResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ConnectionAPI\Response\ConnectionResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Connection\ConnectionService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidConnectionDetailsException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidConnectionModeException;
/**
 * Class ConnectionController
 *
 * @package OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\ConnectionAPI\Controller
 */
class ConnectionController
{
    protected ConnectionService $connectionService;
    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }
    /**
     * @param ConnectionRequest $connectionRequest
     *
     * @return ConnectionResponse
     *
     * @throws InvalidConnectionDetailsException
     * @throws InvalidConnectionModeException
     */
    public function connect(ConnectionRequest $connectionRequest): ConnectionResponse
    {
        $this->connectionService->connect($connectionRequest->transformToDomainModel());
        return new ConnectionResponse();
    }
    /**
     * @return ConnectionConfigResponse
     */
    public function getConnectionConfig(): ConnectionConfigResponse
    {
        return new ConnectionConfigResponse($this->connectionService->getConnectionConfig());
    }
}
