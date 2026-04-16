<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\PaymentLinks;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreatePaymentLinkRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentLinkResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * PaymentLinks client interface.
 */
interface PaymentLinksClientInterface
{
    /**
     * Resource /v2/{merchantId}/paymentlinks - Create payment link
     *
     * @param CreatePaymentLinkRequest $body
     * @param CallContext|null $callContext
     * @return PaymentLinkResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function createPaymentLink(CreatePaymentLinkRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/paymentlinks/{paymentLinkId} - Get payment link by ID
     *
     * @param string $paymentLinkId
     * @param CallContext|null $callContext
     * @return PaymentLinkResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getPaymentLinkById($paymentLinkId, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/paymentlinks/{paymentLinkId}/cancel - Cancel PaymentLink by ID
     *
     * @param string $paymentLinkId
     * @param CallContext|null $callContext
     * @return null
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function cancelPaymentLinkById($paymentLinkId, ?CallContext $callContext = null);
}
