<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\PrivacyPolicy;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiResource;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ErrorResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ResponseClassMap;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ExceptionFactory;
/**
 * PrivacyPolicy client.
 */
class PrivacyPolicyClient extends ApiResource implements PrivacyPolicyClientInterface
{
    /** @var ExceptionFactory|null */
    private $responseExceptionFactory = null;
    /**
     * @inheritdoc
     */
    public function getPrivacyPolicy(GetPrivacyPolicyParams $query, ?CallContext $callContext = null)
    {
        $responseClassMap = new ResponseClassMap();
        $responseClassMap->defaultSuccessResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetPrivacyPolicyResponse';
        $responseClassMap->defaultErrorResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\ErrorResponse';
        try {
            return $this->getCommunicator()->get($responseClassMap, $this->instantiateUri('/v2/{merchantId}/services/privacypolicy'), $this->getClientMetaInfo(), $query, $callContext);
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
