<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\HostedTokenization;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiResource;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ErrorResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ResponseClassMap;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateHostedTokenizationRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ExceptionFactory;
/**
 * HostedTokenization client.
 */
class HostedTokenizationClient extends ApiResource implements HostedTokenizationClientInterface
{
    /** @var ExceptionFactory|null */
    private $responseExceptionFactory = null;
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function createHostedTokenization(CreateHostedTokenizationRequest $body, ?CallContext $callContext = null)
    {
        $responseClassMap = new ResponseClassMap();
        $responseClassMap->defaultSuccessResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateHostedTokenizationResponse';
        $responseClassMap->defaultErrorResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\ErrorResponse';
        try {
            return $this->getCommunicator()->post($responseClassMap, $this->instantiateUri('/v2/{merchantId}/hostedtokenizations'), $this->getClientMetaInfo(), $body, null, $callContext);
        } catch (ErrorResponseException $e) {
            throw $this->getResponseExceptionFactory()->createException($e->getHttpStatusCode(), $e->getErrorResponse(), $callContext);
        }
    }
    /**
     * @inheritdoc
     */
    public function getHostedTokenization($hostedTokenizationId, ?CallContext $callContext = null)
    {
        $this->context['hostedTokenizationId'] = $hostedTokenizationId;
        $responseClassMap = new ResponseClassMap();
        $responseClassMap->defaultSuccessResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetHostedTokenizationResponse';
        $responseClassMap->defaultErrorResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\ErrorResponse';
        try {
            return $this->getCommunicator()->get($responseClassMap, $this->instantiateUri('/v2/{merchantId}/hostedtokenizations/{hostedTokenizationId}'), $this->getClientMetaInfo(), null, $callContext);
        } catch (ErrorResponseException $e) {
            throw $this->getResponseExceptionFactory()->createException($e->getHttpStatusCode(), $e->getErrorResponse(), $callContext);
        }
    }
    /** @return ExceptionFactory */
    private function getResponseExceptionFactory()
    {
        if (is_null($this->responseExceptionFactory)) {
            $this->responseExceptionFactory = new ExceptionFactory();
        }
        return $this->responseExceptionFactory;
    }
}
