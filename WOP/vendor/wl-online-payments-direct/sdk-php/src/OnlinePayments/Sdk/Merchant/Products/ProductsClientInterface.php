<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Products;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\AuthorizationException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\CallContext;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication\InvalidResponseException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetPaymentProductsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentProduct;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentProductNetworksResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\ProductDirectory;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\IdempotenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\PlatformException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ReferenceException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ValidationException;
/**
 * Products client interface.
 */
interface ProductsClientInterface
{
    /**
     * Resource /v2/{merchantId}/products - Get payment products
     *
     * @param GetPaymentProductsParams $query
     * @param CallContext|null $callContext
     * @return GetPaymentProductsResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getPaymentProducts(GetPaymentProductsParams $query, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/products/{paymentProductId} - Get payment product
     *
     * @param int $paymentProductId
     * @param GetPaymentProductParams $query
     * @param CallContext|null $callContext
     * @return PaymentProduct
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getPaymentProduct($paymentProductId, GetPaymentProductParams $query, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/products/{paymentProductId}/networks - Get payment product networks
     *
     * @param int $paymentProductId
     * @param GetPaymentProductNetworksParams $query
     * @param CallContext|null $callContext
     * @return PaymentProductNetworksResponse
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getPaymentProductNetworks($paymentProductId, GetPaymentProductNetworksParams $query, ?CallContext $callContext = null);
    /**
     * Resource /v2/{merchantId}/products/{paymentProductId}/directory - Get payment product directory
     *
     * @param int $paymentProductId
     * @param GetProductDirectoryParams $query
     * @param CallContext|null $callContext
     * @return ProductDirectory
     *
     * @throws IdempotenceException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ReferenceException
     * @throws PlatformException
     * @throws ApiException
     * @throws InvalidResponseException
     */
    function getProductDirectory($paymentProductId, GetProductDirectoryParams $query, ?CallContext $callContext = null);
}
