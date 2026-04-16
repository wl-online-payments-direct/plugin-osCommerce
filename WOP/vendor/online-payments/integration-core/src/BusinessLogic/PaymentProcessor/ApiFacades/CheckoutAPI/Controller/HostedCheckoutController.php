<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Controller;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedCheckout\HostedCheckoutSessionRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Response\HostedCheckoutSessionResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\PaymentProcessor\Services\HostedCheckout\HostedCheckoutService;
/**
 * Class HostedTokenizationController.
 *
 * @package OnlinePayments\Core\BusinessLogic\PaymentProcessor\ApiFacades\CheckoutAPI\Controller
 */
class HostedCheckoutController
{
    private HostedCheckoutService $hostedCheckoutService;
    public function __construct(HostedCheckoutService $hostedCheckoutService)
    {
        $this->hostedCheckoutService = $hostedCheckoutService;
    }
    public function createSession(HostedCheckoutSessionRequest $request): HostedCheckoutSessionResponse
    {
        StoreContext::getInstance()->setOrigin($request->getPaymentProductId() ? 'checkoutPre' : 'checkoutHcp');
        return new HostedCheckoutSessionResponse($this->hostedCheckoutService->createSession($request));
    }
}
