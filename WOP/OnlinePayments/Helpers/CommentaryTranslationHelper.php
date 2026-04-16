<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Helpers;

use common\helpers\Translation;
/**
 * Helper class to translate payment transaction commentary
 */
class CommentaryTranslationHelper
{
    /**
     * Create commentary with translation keys that will be translated on display
     *
     * @param string $fraudResult
     * @param string $liability
     * @param string $exemptionType
     * @return string Commentary with translation keys
     */
    public static function createCommentary(string $fraudResult, string $liability, string $exemptionType): string
    {
        $parts = [];
        $parts[] = 'TEXT_FRAUD_RESULT: ' . $fraudResult;
        $parts[] = 'TEXT_LIABILITY: ' . $liability;
        $parts[] = 'TEXT_THREE_DS_EXEMPTION_TYPE: ' . $exemptionType;
        return implode("\n", $parts);
    }
}
