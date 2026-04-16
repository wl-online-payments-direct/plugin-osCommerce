<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\RequestObject;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ResponseClassMap;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\DataObject;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Logging\CommunicatorLogger;
/**
 * Interface CommunicatorInterface
 *
 * @package OnlinePayments\Sdk
 */
interface CommunicatorInterface
{
    /**
     * @param CommunicatorLogger $communicatorLogger
     */
    public function enableLogging(CommunicatorLogger $communicatorLogger);
    /**
     *
     */
    public function disableLogging();
    /**
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param RequestObject|null $requestParameters
     * @param CallContext|null $callContext
     * @return DataObject
     * @throws ResponseException
     */
    public function get(ResponseClassMap $responseClassMap, $relativeUriPath, $clientMetaInfo = '', ?RequestObject $requestParameters = null, ?CallContext $callContext = null);
    /**
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param RequestObject|null $requestParameters
     * @param CallContext|null $callContext
     * @return DataObject
     * @throws Exception
     */
    public function delete(ResponseClassMap $responseClassMap, $relativeUriPath, $clientMetaInfo = '', ?RequestObject $requestParameters = null, ?CallContext $callContext = null);
    /**
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param DataObject|null $requestBodyObject
     * @param RequestObject|null $requestParameters
     * @param CallContext|null $callContext
     * @return DataObject
     * @throws Exception
     */
    public function post(ResponseClassMap $responseClassMap, $relativeUriPath, $clientMetaInfo = '', $requestBodyObject = null, ?RequestObject $requestParameters = null, ?CallContext $callContext = null);
    /**
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param DataObject|null $requestBodyObject
     * @param RequestObject|null $requestParameters
     * @param CallContext|null $callContext
     * @return DataObject
     * @throws Exception
     */
    public function put(ResponseClassMap $responseClassMap, $relativeUriPath, $clientMetaInfo = '', $requestBodyObject = null, ?RequestObject $requestParameters = null, ?CallContext $callContext = null);
}
