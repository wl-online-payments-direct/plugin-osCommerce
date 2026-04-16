<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\modules\orderPayment\WOP\OnlinePayments\Common\Assets\CommonAsset;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Connection\ActiveConnectionProvider;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Multistore\StoreContext;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentProductId;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ServiceRegister;
class LogoUrlService implements \common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Logo\LogoUrlService
{
    /**
     * @inheritDoc
     */
    public function getHostedCheckoutLogoUrl(): string
    {
        /** @var ActiveConnectionProvider $activeConnectionProvider */
        $activeConnectionProvider = ServiceRegister::getService(ActiveConnectionProvider::class);
        $mode = (string) $activeConnectionProvider->get()->getMode();
        $storeId = StoreContext::getInstance()->getStoreId();
        $url = ImageHandler::getImageUrl((string) PaymentProductId::hostedCheckout(), $mode, $storeId);
        if ($url) {
            return $url;
        }
        return $this->getLogoUrl((string) PaymentProductId::hostedCheckout());
    }
    /**
     * @inheritDoc
     */
    public function getLogoUrl(string $productId): string
    {
        $asset = CommonAsset::register(\Yii::$app->view);
        return $asset->baseUrl . '/images/payment_products/' . $productId . '.svg';
    }
}
