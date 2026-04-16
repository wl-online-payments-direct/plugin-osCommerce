<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Captures\CapturesClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Complete\CompleteClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\HostedCheckout\HostedCheckoutClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\HostedTokenization\HostedTokenizationClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Mandates\MandatesClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\PaymentLinks\PaymentLinksClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Payments\PaymentsClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Payouts\PayoutsClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\PrivacyPolicy\PrivacyPolicyClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\ProductGroups\ProductGroupsClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Products\ProductsClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Refunds\RefundsClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Services\ServicesClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Sessions\SessionsClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Tokens\TokensClientInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\Webhooks\WebhooksClientInterface;
/**
 * Merchant client interface.
 */
interface MerchantClientInterface
{
    /**
     * Resource /v2/{merchantId}/hostedcheckouts
     *
     * @return HostedCheckoutClientInterface
     */
    function hostedCheckout();
    /**
     * Resource /v2/{merchantId}/hostedtokenizations
     *
     * @return HostedTokenizationClientInterface
     */
    function hostedTokenization();
    /**
     * Resource /v2/{merchantId}/payments
     *
     * @return PaymentsClientInterface
     */
    function payments();
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/captures
     *
     * @return CapturesClientInterface
     */
    function captures();
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/refunds
     *
     * @return RefundsClientInterface
     */
    function refunds();
    /**
     * Resource /v2/{merchantId}/payments/{paymentId}/complete
     *
     * @return CompleteClientInterface
     */
    function complete();
    /**
     * Resource /v2/{merchantId}/productgroups
     *
     * @return ProductGroupsClientInterface
     */
    function productGroups();
    /**
     * Resource /v2/{merchantId}/products
     *
     * @return ProductsClientInterface
     */
    function products();
    /**
     * Resource /v2/{merchantId}/services/testconnection
     *
     * @return ServicesClientInterface
     */
    function services();
    /**
     * Resource /v2/{merchantId}/webhooks/validateCredentials
     *
     * @return WebhooksClientInterface
     */
    function webhooks();
    /**
     * Resource /v2/{merchantId}/sessions
     *
     * @return SessionsClientInterface
     */
    function sessions();
    /**
     * Resource /v2/{merchantId}/tokens/{tokenId}
     *
     * @return TokensClientInterface
     */
    function tokens();
    /**
     * Resource /v2/{merchantId}/payouts/{payoutId}
     *
     * @return PayoutsClientInterface
     */
    function payouts();
    /**
     * Resource /v2/{merchantId}/mandates
     *
     * @return MandatesClientInterface
     */
    function mandates();
    /**
     * Resource /v2/{merchantId}/services/privacypolicy
     *
     * @return PrivacyPolicyClientInterface
     */
    function privacyPolicy();
    /**
     * Resource /v2/{merchantId}/paymentlinks
     *
     * @return PaymentLinksClientInterface
     */
    function paymentLinks();
}
