<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Tokens;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateTokenRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreatedTokenResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\TokenResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * Tokens client interface.
 */
interface TokensClientInterface
{
    /**
     * Resource /v2/{merchantId}/tokens/{tokenId} - Get token
     *
     * @param string $tokenId
     * @param CallContext|null $callContext
     * @return TokenResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getToken($tokenId, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/tokens/{tokenId} - Delete token
     *
     * @param string $tokenId
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
    function deleteToken($tokenId, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/tokens - Create token
     *
     * @param CreateTokenRequest $body
     * @param CallContext|null $callContext
     * @return CreatedTokenResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function createToken(CreateTokenRequest $body, ?CallContext $callContext = null);
}
