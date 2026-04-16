<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Payouts;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiResource;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ErrorResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ResponseClassMap;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreatePayoutRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ExceptionFactory;
/**
 * Payouts client.
 */
class PayoutsClient extends ApiResource implements PayoutsClientInterface
{
    /** @var ExceptionFactory|null */
    private $responseExceptionFactory = null;
    /**
     * @inheritdoc
     */
    public function getPayout($payoutId, ?CallContext $callContext = null)
    {
        $this->context['payoutId'] = $payoutId;
        $responseClassMap = new ResponseClassMap();
        $responseClassMap->defaultSuccessResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PayoutResponse';
        $responseClassMap->defaultErrorResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\ErrorResponse';
        try {
            return $this->getCommunicator()->get($responseClassMap, $this->instantiateUri('/v2/{merchantId}/payouts/{payoutId}'), $this->getClientMetaInfo(), null, $callContext);
        } catch (ErrorResponseException $e) {
            throw $this->getResponseExceptionFactory()->createException($e->getHttpStatusCode(), $e->getErrorResponse(), $callContext);
        }
    }
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function createPayout(CreatePayoutRequest $body, ?CallContext $callContext = null)
    {
        $responseClassMap = new ResponseClassMap();
        $responseClassMap->defaultSuccessResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PayoutResponse';
        $responseClassMap->defaultErrorResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PayoutErrorResponse';
        try {
            return $this->getCommunicator()->post($responseClassMap, $this->instantiateUri('/v2/{merchantId}/payouts'), $this->getClientMetaInfo(), $body, null, $callContext);
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
