<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Mandates;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateMandateRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateMandateResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetMandateResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * Mandates client interface.
 */
interface MandatesClientInterface
{
    /**
     * Resource /v2/{merchantId}/mandates - Create mandate
     *
     * @param CreateMandateRequest $body
     * @param CallContext|null $callContext
     * @return CreateMandateResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function createMandate(CreateMandateRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/mandates/{uniqueMandateReference} - Get mandate
     *
     * @param string $uniqueMandateReference
     * @param CallContext|null $callContext
     * @return GetMandateResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getMandate($uniqueMandateReference, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/mandates/{uniqueMandateReference}/block - Block mandate
     *
     * @param string $uniqueMandateReference
     * @param CallContext|null $callContext
     * @return GetMandateResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function blockMandate($uniqueMandateReference, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/mandates/{uniqueMandateReference}/unblock - Unblock mandate
     *
     * @param string $uniqueMandateReference
     * @param CallContext|null $callContext
     * @return GetMandateResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function unblockMandate($uniqueMandateReference, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/mandates/{uniqueMandateReference}/revoke - Revoke mandate
     *
     * @param string $uniqueMandateReference
     * @param CallContext|null $callContext
     * @return GetMandateResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function revokeMandate($uniqueMandateReference, ?CallContext $callContext = null);
}
