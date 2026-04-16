<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Refunds;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\RefundsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * Refunds client interface.
 */
interface RefundsClientInterface
{
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/refunds - Get refunds of payment
     *
     * @param string $paymentId
     * @param CallContext|null $callContext
     * @return RefundsResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getRefunds($paymentId, ?CallContext $callContext = null);
}
