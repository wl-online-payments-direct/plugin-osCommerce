<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Examples;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ApiException;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\ClientTestCase;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\Card;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateTokenRequest;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\TokenCardSpecificInput;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\TokenData;
/**
 * @group examples
 *
 */
class TokenTest extends ClientTestCase
{
    /**
     * @return string
     * @throws ApiException|Exception
     */
    public function testCreateToken()
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        $createTokenRequest = new CreateTokenRequest();
        $createTokenRequest->setPaymentProductId(1);
        $cardSpecificInput = new TokenCardSpecificInput();
        $data = new TokenData();
        $card = new Card();
        $card->setCardholderName("Wile E. Coyote");
        $card->setCardNumber("4567350000427977");
        $card->setCvv("123");
        $card->setExpiryDate("1230");
        $data->setCard($card);
        $cardSpecificInput->setData($data);
        $createTokenRequest->setCard($cardSpecificInput);
        $createTokenResponse = $client->merchant($merchantId)->tokens()->createToken($createTokenRequest);
        return $createTokenResponse->getToken();
    }
    /**
     * @param string $token
     * @return string
     * @throws ApiException|Exception
     * @depends testCreateToken
     */
    public function testRetrieveToken($token)
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        $client->merchant($merchantId)->tokens()->getToken($token);
        return $token;
    }
    /**
     * @depends testRetrieveToken
     * @param string $token
     * @throws ApiException|Exception
     */
    public function testDeleteToken($token)
    {
        $this->expectNotToPerformAssertions();
        $client = $this->getClient();
        $merchantId = $this->getMerchantId();
        $client->merchant($merchantId)->tokens()->deleteToken($token);
    }
}
