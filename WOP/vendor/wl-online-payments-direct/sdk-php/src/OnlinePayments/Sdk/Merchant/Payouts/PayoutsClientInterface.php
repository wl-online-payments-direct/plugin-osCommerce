<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Payouts;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\DeclinedPayoutException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreatePayoutRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PayoutResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * Payouts client interface.
 */
interface PayoutsClientInterface
{
    /**
     * Resource /v2/{merchantId}/payouts/{payoutId} - Get payout
     *
     * @param string $payoutId
     * @param CallContext|null $callContext
     * @return PayoutResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getPayout($payoutId, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/payouts - Create payout
     *
     * @param CreatePayoutRequest $body
     * @param CallContext|null $callContext
     * @return PayoutResponse
     *
     * @throws DeclinedPayoutException
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function createPayout(CreatePayoutRequest $body, ?CallContext $callContext = null);
}
