<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Captures;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiResource;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ErrorResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ResponseClassMap;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ExceptionFactory;
/**
 * Captures client.
 */
class CapturesClient extends ApiResource implements CapturesClientInterface
{
    /** @var ExceptionFactory|null */
    private $responseExceptionFactory = null;
    /**
     * @inheritdoc
     */
    public function getCaptures($paymentId, ?CallContext $callContext = null)
    {
        $this->context['paymentId'] = $paymentId;
        $responseClassMap = new ResponseClassMap();
        $responseClassMap->defaultSuccessResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CapturesResponse';
        $responseClassMap->defaultErrorResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\ErrorResponse';
        try {
            return $this->getCommunicator()->get($responseClassMap, $this->instantiateUri('/v2/{merchantId}/payments/{paymentId}/captures'), $this->getClientMetaInfo(), null, $callContext);
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
