<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Checkout;

use common\classes\extended\OrderAbstract;
use common\classes\Order;
use common\classes\TmpOrder;
use common\helpers\Language;
use common\helpers\System;
use common\services\OrderManager;
use frontend\design\Info;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Amount;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\Address;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\Cart;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\CartProvider as CartProviderInterface;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\Customer\ContactDetails;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\Customer\Customer;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\Customer\Device;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\Customer\PersonalInformation;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\LineItem;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\LineItemCollection;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\Product;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Cart\Shipping;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Country;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\Currency;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\TaxableAmount;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Checkout\TaxRate;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Class CartProvider.
 *
 * @package OnlinePayments\Services\Checkout
 */
class CartProvider implements CartProviderInterface
{
    private OrderManager $manager;
    public function __construct(OrderManager $orderManager)
    {
        $this->manager = $orderManager;
    }
    public function get(): Cart
    {
        /** @var OrderAbstract $order */
        $order = $this->manager->getParentToInstance(TmpOrder::class);
        $currencies = \Yii::$container->get('currencies');
        $totalAmount = Amount::fromFloat($order->info['total_inc_tax'] * $order->info['currency_value'], Currency::fromIsoCode($order->info['currency']));
        $totalAmountIinEuro = null;
        if (array_key_exists('EUR', $currencies->currencies)) {
            $totalAmountIinEuro = Amount::fromFloat($order->info['total_inc_tax'] * $currencies->currencies['EUR']['value'], Currency::fromIsoCode('EUR'));
        }
        $customer = $this->manager->getCustomersIdentity();
        $billingAddress = !empty($order->billing) ? $order->billing : $this->manager->getBillingAddress();
        $orderId = (string) $order->getOrderId();
        if (Info::isAdmin() && !$order->getOrderId()) {
            $requestOrderId = \Yii::$app->request->get('orders_id');
            if (!$requestOrderId) {
                $manager = OrderManager::loadManager();
                $orderModel = Order::getARModel()->where(['basket_id' => $manager->get('cart')->basketID])->one();
                $requestOrderId = $orderModel->orders_id;
            }
            $tmpOrderModel = TmpOrder::getARModel()->where(['child_id' => $requestOrderId])->one();
            $orderId = $tmpOrderModel ? $tmpOrderModel->orders_id : $orderId;
        }
        return new Cart($orderId, $totalAmount, $totalAmountIinEuro, new Customer(new ContactDetails((string) ($order->customer ? $order->customer['email_address'] : $customer->customers_email_address), (string) ($order->customer ? $order->customer['telephone'] : $customer->customers_telephone)), $this->getAddress($billingAddress), $this->manager->isCustomerAssigned() ? $this->manager->getCustomerAssigned() : '', !$this->manager->isCustomerAssigned(), (string) Language::get_language_code($this->manager->getCart()->language_id, \false), $this->getDeviceData()), $this->getLineItems(), new Shipping(TaxableAmount::fromAmounts(Amount::fromFloat($order->info['shipping_cost_exc_tax'] * $order->info['currency_value'], Currency::fromIsoCode($order->info['currency'])), Amount::fromFloat($order->info['shipping_cost_inc_tax'] * $order->info['currency_value'], Currency::fromIsoCode($order->info['currency']))), $this->getAddress(!empty($order->delivery) ? $order->delivery : $billingAddress), new ContactDetails((string) (!empty($order->delivery['email_address']) ? $order->delivery['email_address'] : $customer->customers_email_address))), Amount::fromFloat(($order->info['total_inc_tax'] - $order->info['subtotal_inc_tax'] - $order->info['shipping_cost_inc_tax']) * $order->info['currency_value'], Currency::fromIsoCode($order->info['currency'])));
    }
    private function getLineItems(): ?LineItemCollection
    {
        $order = $this->manager->getOrderInstance();
        $lineItems = new LineItemCollection();
        foreach ($order->products as $product) {
            $lineItems->add(new LineItem(new Product((string) $product['id'], (string) $product['name'], (string) $product['model']), TaxableAmount::fromAmountExclTaxAndTaxRate(Amount::fromFloat($product['price'] * $order->info['currency_value'], Currency::fromIsoCode($order->info['currency'])), new TaxRate($product['tax'])), (int) $product['qty']));
        }
        return $lineItems;
    }
    private function getAddress(array $address): Address
    {
        return new Address(Country::fromIsoCode($address['country']['iso_code_2']), (string) $address['state'], (string) $address['city'], (string) $address['postcode'], (string) $address['street_address'], '', new PersonalInformation((string) $address['firstname'], (string) $address['lastname'], (string) $address['gender']), (string) $address['company'], (string) $address['suburb']);
    }
    private function getDeviceData(): Device
    {
        $extraParams = ['color_depth' => 24, 'screen_height' => '1080', 'screen_width' => '1920', 'timezone_offset_utc_minutes' => '', 'java_enabled' => \false];
        foreach ($extraParams as $key => $defaultValue) {
            $value = null;
            $prefix = ModuleHelper::addModuleNamePrefix($key);
            if ($this->manager->has($prefix)) {
                $value = $this->manager->get($prefix);
            }
            // OsCommerce adds this prefix if one-page checkout is enabled
            $prefix = 'one_page_checkout_' . $prefix;
            if ($this->manager->has($prefix)) {
                $value = $this->manager->get($prefix);
            }
            if (!empty($value)) {
                $extraParams[$key] = $value;
            }
        }
        return new Device(\Yii::$app->getRequest()->headers->get('Accept'), \Yii::$app->getRequest()->headers->get('User-Agent'), System::get_ip_address(), (int) $extraParams['color_depth'], (string) $extraParams['screen_height'], (string) $extraParams['screen_width'], (string) $extraParams['timezone_offset_utc_minutes'], (bool) $extraParams['java_enabled']);
    }
}
