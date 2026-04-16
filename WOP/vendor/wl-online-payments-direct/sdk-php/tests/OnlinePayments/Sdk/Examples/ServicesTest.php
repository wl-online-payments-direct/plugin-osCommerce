<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Examples;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ClientTestCase;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetIINDetailsRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetIINDetailsResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\TestConnection;
/**
 * @group examples
 *
 */
class ServicesTest extends ClientTestCase
{
    /**
     * @return TestConnection
     * @throws Exception
     */
    public function testTestConnection()
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        return $client->merchant($merchantId)->services()->testConnection();
    }
    /**
     * @return GetIINDetailsResponse
     * @throws Exception
     */
    public function testRetrieveIINDetails()
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        $body = new GetIINDetailsRequest();
        $body->setBin("401200");
        return $client->merchant($merchantId)->services()->getIINdetails($body);
    }
}
