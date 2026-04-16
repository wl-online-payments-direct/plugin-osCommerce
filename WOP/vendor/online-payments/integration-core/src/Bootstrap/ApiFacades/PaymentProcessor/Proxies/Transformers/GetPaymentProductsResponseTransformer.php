<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethod;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethodCollection;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentProductId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Translations\Model\Translation;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Domain\GetPaymentProductsResponse;
/**
 * Class GetPaymentProductsResponseTransformer.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\PaymentProcessor\Proxies\Transformers
 */
class GetPaymentProductsResponseTransformer
{
    public static function transform(GetPaymentProductsResponse $response): PaymentMethodCollection
    {
        $result = new PaymentMethodCollection();
        foreach ($response->getPaymentProducts() ?? [] as $paymentProduct) {
            if (PaymentProductId::isSupported((string) $paymentProduct->id)) {
                $result->add(new PaymentMethod(PaymentProductId::parse((string) $paymentProduct->id), new TranslationCollection(new Translation('EN', $paymentProduct->displayHints->label)), \true));
            }
        }
        return $result;
    }
}
