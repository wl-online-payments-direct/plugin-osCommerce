<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Payments;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\DeclinedPaymentException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\DeclinedRefundException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CancelPaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CancelPaymentResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CapturePaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CaptureResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreatePaymentResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\RefundRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\RefundResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\SubsequentPaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\SubsequentPaymentResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * Payments client interface.
 */
interface PaymentsClientInterface
{
    /**
     * Resource /v2/{merchantId}/payments - Create payment
     *
     * @param CreatePaymentRequest $body
     * @param CallContext|null $callContext
     * @return CreatePaymentResponse
     *
     * @throws DeclinedPaymentException
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function createPayment(CreatePaymentRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/payments/{paymentId} - Get payment
     *
     * @param string $paymentId
     * @param CallContext|null $callContext
     * @return PaymentResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getPayment($paymentId, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/details - Get payment details
     *
     * @param string $paymentId
     * @param CallContext|null $callContext
     * @return PaymentDetailsResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getPaymentDetails($paymentId, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/cancel - Cancel payment
     *
     * @param string $paymentId
     * @param CancelPaymentRequest $body
     * @param CallContext|null $callContext
     * @return CancelPaymentResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function cancelPayment($paymentId, CancelPaymentRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/capture - Capture payment
     *
     * @param string $paymentId
     * @param CapturePaymentRequest $body
     * @param CallContext|null $callContext
     * @return CaptureResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function capturePayment($paymentId, CapturePaymentRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/refund - Refund payment
     *
     * @param string $paymentId
     * @param RefundRequest $body
     * @param CallContext|null $callContext
     * @return RefundResponse
     *
     * @throws DeclinedRefundException
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function refundPayment($paymentId, RefundRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/subsequent - Subsequent payment
     *
     * @param string $paymentId
     * @param SubsequentPaymentRequest $body
     * @param CallContext|null $callContext
     * @return SubsequentPaymentResponse
     *
     * @throws DeclinedPaymentException
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function subsequentPayment($paymentId, SubsequentPaymentRequest $body, ?CallContext $callContext = null);
}
