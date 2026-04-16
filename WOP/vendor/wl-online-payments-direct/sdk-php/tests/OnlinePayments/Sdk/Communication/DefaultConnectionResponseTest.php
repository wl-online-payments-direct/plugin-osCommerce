<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk\Communication;

use common\modules\orderPayment\WOP\PHPUnit\Framework\TestCase;
/**
 * @group default_connection
 *
 */
class DefaultConnectionResponseTest extends TestCase
{
    public function testGetters()
    {
        $httpStatusCode = 123;
        $headers = array(0 => 'Foo', 'Bar' => 'Baz');
        $body = "Foo Bar\nBáz";
        $connectionResponse = new ConnectionResponse($httpStatusCode, $headers, $body);
        $this->assertEquals($httpStatusCode, $connectionResponse->getHttpStatusCode());
        $this->assertEquals($headers, $connectionResponse->getHeaders());
        $this->assertEquals($body, $connectionResponse->getBody());
        // @phpstan-ignore-next-line
        $this->assertEquals('Foo', $connectionResponse->getHeaderValue(0));
        $this->assertEquals('Baz', $connectionResponse->getHeaderValue('Bar'));
        $this->assertEquals('Baz', $connectionResponse->getHeaderValue('bar'));
        // @phpstan-ignore-next-line
        $this->assertEquals('', $connectionResponse->getHeaderValue(1));
        $this->assertEquals('', $connectionResponse->getHeaderValue('baz'));
    }
}
