<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedCheckout;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\CartProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\MemoryCachingCartProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\RoundingTotalsCartProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentProductId;
/**
 * Class HostedCheckoutSessionRequest.
 *
 * @package OnlinePayments\Core\BusinessLogic\Domain\HostedCheckout
 */
class HostedCheckoutSessionRequest
{
    private ?PaymentProductId $paymentProductId;
    private CartProvider $cartProvider;
    private string $returnUrl;
    private ?string $tokenId;
    public function __construct(CartProvider $cart, string $returnUrl, ?PaymentProductId $paymentProductId = null, ?string $tokenId = null)
    {
        $this->cartProvider = new MemoryCachingCartProvider(new RoundingTotalsCartProvider($cart));
        $this->returnUrl = $returnUrl;
        $this->paymentProductId = $paymentProductId;
        $this->tokenId = $tokenId;
    }
    public function getPaymentProductId(): ?PaymentProductId
    {
        return $this->paymentProductId;
    }
    public function getCartProvider(): CartProvider
    {
        return $this->cartProvider;
    }
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
    public function getTokenId(): ?string
    {
        return $this->tokenId;
    }
}
