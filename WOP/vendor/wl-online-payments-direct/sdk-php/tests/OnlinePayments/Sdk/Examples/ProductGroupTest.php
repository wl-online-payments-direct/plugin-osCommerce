<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Examples;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ClientTestCase;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetPaymentProductGroupsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\PaymentProductGroup;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\ProductGroups\GetProductGroupParams;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\ProductGroups\GetProductGroupsParams;
/**
 * @group examples
 *
 */
class ProductGroupTest extends ClientTestCase
{
    /**
     * @return GetPaymentProductGroupsResponse
     * @throws Exception
     */
    public function testRetrievePaymentProductGroups()
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        $findParams = new GetProductGroupsParams();
        $findParams->setAmount(1000);
        $findParams->setCurrencyCode("EUR");
        $findParams->setCountryCode("NL");
        $findParams->addHide("fields");
        $findParams->setIsRecurring(\true);
        $findParams->setLocale("en_US");
        return $client->merchant($merchantId)->productGroups()->getProductGroups($findParams);
    }
    /**
     * @return PaymentProductGroup
     * @throws Exception
     * @throws ApiException
     */
    public function testRetrievePaymentProductGroup()
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        $getParams = new GetProductGroupParams();
        $getParams->setAmount(1000);
        $getParams->setCountryCode("NL");
        $getParams->setCurrencyCode("EUR");
        $getParams->setLocale("en_US");
        $getParams->setIsRecurring(\true);
        return $client->merchant($merchantId)->productGroups()->getProductGroup("cards", $getParams);
    }
}
