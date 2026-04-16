<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Services;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CalculateSurchargeRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CalculateSurchargeResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CurrencyConversionRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CurrencyConversionResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetIINDetailsRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetIINDetailsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\TestConnection;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * Services client interface.
 */
interface ServicesClientInterface
{
    /**
     * Resource /v2/{merchantId}/services/testconnection - Test connection
     *
     * @param CallContext|null $callContext
     * @return TestConnection
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function testConnection(?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/services/getIINdetails - Get IIN details
     *
     * @param GetIINDetailsRequest $body
     * @param CallContext|null $callContext
     * @return GetIINDetailsResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getIINDetails(GetIINDetailsRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/services/dccrate - Get currency conversion quote
     *
     * @param CurrencyConversionRequest $body
     * @param CallContext|null $callContext
     * @return CurrencyConversionResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getDccRateInquiry(CurrencyConversionRequest $body, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/services/surchargecalculation - Surcharge Calculation
     *
     * @param CalculateSurchargeRequest $body
     * @param CallContext|null $callContext
     * @return CalculateSurchargeResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function surchargeCalculation(CalculateSurchargeRequest $body, ?CallContext $callContext = null);
}
