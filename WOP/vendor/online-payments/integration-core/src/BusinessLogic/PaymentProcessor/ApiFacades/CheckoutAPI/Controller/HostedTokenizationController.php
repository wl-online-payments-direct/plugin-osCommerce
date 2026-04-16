<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Controller;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\CartProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\Exceptions\TokenDeletionFailureException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\Exceptions\TokenNotFoundException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\PaymentRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentProductId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response\HostedTokenizationResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response\PayResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response\TokenDeleteResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response\TokensResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\HostedTokenization\HostedTokenizationService;
/**
 * Class HostedTokenizationController.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Controller
 */
class HostedTokenizationController
{
    private HostedTokenizationService $hostedTokenizationService;
    public function __construct(HostedTokenizationService $hostedTokenizationService)
    {
        $this->hostedTokenizationService = $hostedTokenizationService;
    }
    public function crate(CartProvider $cartProvider, ?PaymentProductId $productId = null): HostedTokenizationResponse
    {
        StoreContext::getInstance()->setOrigin('checkoutHtp');
        return new HostedTokenizationResponse($this->hostedTokenizationService->create($cartProvider, $productId));
    }
    public function pay(PaymentRequest $paymentRequest): PayResponse
    {
        StoreContext::getInstance()->setOrigin($paymentRequest->getTokenId() ? 'checkoutHtpStored' : 'checkoutHtpNew');
        return new PayResponse($this->hostedTokenizationService->pay($paymentRequest));
    }
    public function getTokens(string $customerId): TokensResponse
    {
        StoreContext::getInstance()->setOrigin('storedCards');
        return new TokensResponse($this->hostedTokenizationService->getTokens($customerId));
    }
    /**
     * @param string $customerId
     * @param string $tokenId
     *
     * @return TokenDeleteResponse
     *
     * @throws TokenDeletionFailureException
     * @throws TokenNotFoundException
     */
    public function deleteToken(string $customerId, string $tokenId): TokenDeleteResponse
    {
        StoreContext::getInstance()->setOrigin('storedCards');
        $this->hostedTokenizationService->deleteToken($customerId, $tokenId);
        return new TokenDeleteResponse();
    }
}
