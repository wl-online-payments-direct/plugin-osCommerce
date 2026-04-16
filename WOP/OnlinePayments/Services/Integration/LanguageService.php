<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\models\Languages;
use common\modules\orderPayment\WOP\OnlinePayments\Common\Assets\CommonAsset;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Language\LanguageService as CoreLanguageService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Language\Exception\InvalidIsoCodeException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Language\Language;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Language\LanguageCode;
class LanguageService implements CoreLanguageService
{
    /**
     * @inheritDoc
     */
    public function getEnabledLanguages(): array
    {
        $asset = CommonAsset::register(\Yii::$app->view);
        $baseImageUrl = $asset->baseUrl . '/images';
        $languages = Languages::find()->where(['languages_status' => 1])->asArray()->all();
        $result = [];
        foreach ($languages as $language) {
            $languageCode = $this->getLanguageCode($language['code']);
            $imageName = $languageCode ? $this->getImageName($languageCode) : '';
            $result[] = new Language(strtoupper($language['code']), $imageName ? $baseImageUrl . '/flags/' . $imageName . '.svg' : '');
        }
        return $result;
    }
    private function getLanguageCode(string $isoCode): ?LanguageCode
    {
        try {
            return LanguageCode::fromIso($isoCode);
        } catch (InvalidIsoCodeException $e) {
            return null;
        }
    }
    private function getImageName(LanguageCode $iso): string
    {
        return strtolower('country-' . $iso->getCountry());
    }
}
