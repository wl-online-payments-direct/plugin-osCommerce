<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Examples;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ClientTestCase;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\Address;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\AmountOfMoney;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateHostedCheckoutResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\Customer;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetHostedCheckoutResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInput;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\Order;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentProductFilter;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentProductFiltersHostedCheckout;
/**
 * @group examples
 *
 */
class HostedCheckoutTest extends ClientTestCase
{
    /**
     * HOSTED CHECKOUT
     */
    /**
     * @return string
     * @throws Exception
     * @throws ApiException
     */
    public function testCreateHostedCheckout()
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        $createHostedCheckoutRequest = new CreateHostedCheckoutRequest();
        $order = new Order();
        $amountOfMoney = new AmountOfMoney();
        $amountOfMoney->setCurrencyCode("EUR");
        $amountOfMoney->setAmount(2345);
        $order->setAmountOfMoney($amountOfMoney);
        $customer = new Customer();
        $customer->setMerchantCustomerId("123456789");
        $billingAddress = new Address();
        $billingAddress->setCountryCode("NL");
        $customer->setBillingAddress($billingAddress);
        $order->setCustomer($customer);
        $createHostedCheckoutRequest->setOrder($order);
        $hostedCheckoutSpecificInput = new HostedCheckoutSpecificInput();
        $hostedCheckoutSpecificInput->setLocale("en_GB");
        $hostedCheckoutSpecificInput->setVariant("100");
        $hostedCheckoutSpecificInput->setPaymentProductFilters(new PaymentProductFiltersHostedCheckout());
        $hostedCheckoutSpecificInput->getPaymentProductFilters()->setExclude(new PaymentProductFilter());
        $hostedCheckoutSpecificInput->getPaymentProductFilters()->getExclude()->setProducts(array(120));
        $createHostedCheckoutRequest->setHostedCheckoutSpecificInput($hostedCheckoutSpecificInput);
        /** @var CreateHostedCheckoutResponse $createHostedCheckoutResponse */
        $createHostedCheckoutResponse = $client->merchant($merchantId)->hostedCheckout()->createHostedCheckout($createHostedCheckoutRequest);
        return $createHostedCheckoutResponse->getHostedCheckoutId();
    }
    /**
     * @depends testCreateHostedCheckout
     * @param string $hostedCheckoutId
     * @return string
     * @throws ApiException
     * @throws Exception
     */
    public function testGetHostedCheckoutStatus($hostedCheckoutId)
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        /** @var GetHostedCheckoutResponse $getHostedCheckoutResponse */
        $getHostedCheckoutResponse = $client->merchant($merchantId)->hostedCheckout()->getHostedCheckout($hostedCheckoutId);
        return $getHostedCheckoutResponse->getStatus();
    }
}
