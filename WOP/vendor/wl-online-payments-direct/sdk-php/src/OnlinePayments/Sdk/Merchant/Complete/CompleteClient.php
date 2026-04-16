<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Complete;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiResource;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ErrorResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\ResponseClassMap;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CompletePaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ExceptionFactory;
/**
 * Complete client.
 */
class CompleteClient extends ApiResource implements CompleteClientInterface
{
    /** @var ExceptionFactory|null */
    private $responseExceptionFactory = null;
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function completePayment($paymentId, CompletePaymentRequest $body, ?CallContext $callContext = null)
    {
        $this->context['paymentId'] = $paymentId;
        $responseClassMap = new ResponseClassMap();
        $responseClassMap->defaultSuccessResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CompletePaymentResponse';
        $responseClassMap->defaultErrorResponseClassName = 'common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentErrorResponse';
        try {
            return $this->getCommunicator()->post($responseClassMap, $this->instantiateUri('/v2/{merchantId}/payments/{paymentId}/complete'), $this->getClientMetaInfo(), $body, null, $callContext);
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
