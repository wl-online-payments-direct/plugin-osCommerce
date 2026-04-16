<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Examples;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ClientTestCase;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\AmountOfMoney;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\RefundRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\RefundResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
/**
 * @group examples
 *
 */
class RefundTest extends ClientTestCase
{
    /**
     * @return string
     * @return string
     * @throws ApiException*@throws \Exception
     * @throws Exception
     */
    public function testCreateRefund()
    {
        $this->markTestSkipped('No refundable payment is available at this time');
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        $paymentId = "1_1";
        $refundRequest = new RefundRequest();
        $amountOfMoney = new AmountOfMoney();
        $amountOfMoney->setCurrencyCode("EUR");
        $amountOfMoney->setAmount(1);
        $refundRequest->setAmountOfMoney($amountOfMoney);
        /** @var RefundResponse $refundResponse * */
        $refundResponse = $client->merchant($merchantId)->payments()->refundPayment($paymentId, $refundRequest);
        return $refundResponse->getId();
    }
}
