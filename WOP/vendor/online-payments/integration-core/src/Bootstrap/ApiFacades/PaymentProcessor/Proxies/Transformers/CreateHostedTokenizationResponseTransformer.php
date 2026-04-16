<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\HostedTokenization;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\CreateHostedTokenizationResponse;
/**
 * Class CreateHostedTokenizationResponseTransformer.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers
 */
class CreateHostedTokenizationResponseTransformer
{
    public static function transform(CreateHostedTokenizationResponse $response): HostedTokenization
    {
        return new HostedTokenization((string) $response->getHostedTokenizationUrl(), $response->getInvalidTokens() ?? []);
    }
}
