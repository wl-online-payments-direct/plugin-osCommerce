<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Controller;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Request\PaymentMethodRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Response\PaymentMethodEnableResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Response\PaymentMethodResponse as ApiPaymentMethodResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Response\PaymentMethodSaveResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Response\PaymentMethodsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\AdminConfig\Services\Payment\PaymentService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidActionTypeException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidAutomaticCaptureValueException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidExemptionTypeException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidPaymentAttemptsNumberException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidFlowTypeException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidPaymentProductIdException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidRecurrenceTypeException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidSessionTimeoutException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidSignatureTypeException;
/**
 * Class PaymentController
 *
 * @package OnlinePayments\Core\BusinessLogic\AdminConfig\ApiFacades\PaymentAPI\Controller
 */
class PaymentController
{
    protected PaymentService $paymentService;
    protected ShopPaymentService $shopPaymentService;
    /**
     * @param PaymentService $paymentService
     * @param ShopPaymentService $shopPaymentService
     */
    public function __construct(PaymentService $paymentService, ShopPaymentService $shopPaymentService)
    {
        $this->paymentService = $paymentService;
        $this->shopPaymentService = $shopPaymentService;
    }
    /**
     * @return PaymentMethodsResponse
     * @throws InvalidPaymentProductIdException
     * @throws InvalidSessionTimeoutException
     * @throws InvalidAutomaticCaptureValueException
     * @throws InvalidPaymentAttemptsNumberException
     */
    public function list(): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse($this->paymentService->getPaymentMethods());
    }
    /**
     * @param string $paymentProductId
     * @param bool $enabled
     *
     * @return PaymentMethodEnableResponse
     *
     * @throws InvalidAutomaticCaptureValueException
     * @throws InvalidPaymentAttemptsNumberException
     * @throws InvalidPaymentProductIdException
     * @throws InvalidSessionTimeoutException
     */
    public function enable(string $paymentProductId, bool $enabled): PaymentMethodEnableResponse
    {
        $this->paymentService->enablePaymentMethod($paymentProductId, $enabled);
        $this->shopPaymentService->enable($paymentProductId, $enabled);
        return new PaymentMethodEnableResponse();
    }
    /**
     * @param PaymentMethodRequest $paymentMethodRequest
     *
     * @return PaymentMethodSaveResponse
     *
     * @throws InvalidPaymentProductIdException
     * @throws InvalidRecurrenceTypeException
     * @throws InvalidSessionTimeoutException
     * @throws InvalidSignatureTypeException
     * @throws InvalidCurrencyCode
     * @throws InvalidActionTypeException
     * @throws InvalidExemptionTypeException
     * @throws InvalidFlowTypeException
     */
    public function save(PaymentMethodRequest $paymentMethodRequest): PaymentMethodSaveResponse
    {
        $method = $paymentMethodRequest->transformToDomainModel();
        $this->paymentService->savePaymentMethod($method);
        $this->shopPaymentService->savePaymentMethod($method);
        return new PaymentMethodSaveResponse();
    }
    /**
     * @param string $paymentProductId
     *
     * @return ApiPaymentMethodResponse
     * @throws InvalidAutomaticCaptureValueException
     * @throws InvalidPaymentAttemptsNumberException
     * @throws InvalidPaymentProductIdException
     * @throws InvalidSessionTimeoutException
     */
    public function getPaymentMethod(string $paymentProductId): ApiPaymentMethodResponse
    {
        return new ApiPaymentMethodResponse($this->paymentService->getPaymentMethod($paymentProductId));
    }
}
