<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\HostedTokenization;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateHostedTokenizationRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateHostedTokenizationResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetHostedTokenizationResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * HostedTokenization client interface.
 */
interface HostedTokenizationClientInterface
{
    /**
     * Resource /v2/{merchantId}/hostedtokenizations - Create hosted tokenization session
     *
     * @param CreateHostedTokenizationRequest $body
     * @param CallContext|null $callContext
     * @return CreateHostedTokenizationResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function createHostedTokenization(CreateHostedTokenizationRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/hostedtokenizations/{hostedTokenizationId} - Get hosted tokenization session
     *
     * @param string $hostedTokenizationId
     * @param CallContext|null $callContext
     * @return GetHostedTokenizationResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getHostedTokenization($hostedTokenizationId, ?CallContext $callContext = null);
}
