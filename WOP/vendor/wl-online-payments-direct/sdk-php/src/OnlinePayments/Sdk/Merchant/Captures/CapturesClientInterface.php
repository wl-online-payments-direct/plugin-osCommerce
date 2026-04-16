<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Captures;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CapturesResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * Captures client interface.
 */
interface CapturesClientInterface
{
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/captures - Get captures of payment
     *
     * @param string $paymentId
     * @param CallContext|null $callContext
     * @return CapturesResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getCaptures($paymentId, ?CallContext $callContext = null);
}
