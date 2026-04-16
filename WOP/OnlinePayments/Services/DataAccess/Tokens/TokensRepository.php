<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\DataAccess\Tokens;

use common\models\PaymentTokens;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\DataAccess\Tokens\TokensRepository as CoreTokensRepository;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\HostedTokenization\Token;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\PaymentMethod\PaymentMethodDefaultConfigs;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
/**
 * Class TokensRepository.
 *
 * @package OnlinePayments\Services\DataAccess\Tokens
 */
class TokensRepository extends CoreTokensRepository
{
    public function save(string $getCustomerId, Token $token): void
    {
        parent::save($getCustomerId, $token);
        $existingToken = $this->findMatchingToken($token);
        $expYear = '20' . substr($token->getExpiryDate(), -2);
        $expMonth = substr($token->getExpiryDate(), 0, 2);
        $tokensModel = new PaymentTokens();
        if ($existingToken) {
            $tokensModel->payment_tokens_id = $existingToken->payment_tokens_id;
        }
        $tokensModel->customers_id = (int) $token->getCustomerId();
        $tokensModel->payment_class = ModuleHelper::getModuleConfig()->getModuleName();
        $tokensModel->token = $token->getTokenId();
        $tokensModel->card_type = strtolower(PaymentMethodDefaultConfigs::getName($token->getProductId(), ModuleHelper::getModuleConfig()->getBrand())['translation']);
        $tokensModel->exp_date = date('Y-m-t', mktime(23, 59, 59, intval($expMonth), 1, $expYear));
        $tokensModel->last_digits = $token->getCardNumber();
        $tokensModel->save();
    }
    /**
     * @param Token[] $tokens
     * @return void
     */
    public function delete(array $tokens): void
    {
        parent::delete($tokens);
        foreach ($tokens as $token) {
            PaymentTokens::deleteToken((int) $token->getCustomerId(), ModuleHelper::getModuleConfig()->getModuleName(), $token->getTokenId());
        }
    }
    /**
     * We must search trough all tokens because tokens are encrypted in the DB.
     * Same principe is done by osCommerce in PaymentTokens::deleteToken implementation
     *
     * @param Token $token
     * @return PaymentTokens|null
     */
    private function findMatchingToken(Token $token): ?PaymentTokens
    {
        $existingTokens = PaymentTokens::find()->where(['customers_id' => (int) $token->getCustomerId(), 'payment_class' => ModuleHelper::getModuleConfig()->getModuleName()])->all();
        foreach ($existingTokens as $existingToken) {
            if ($existingToken->token == $token->getTokenId()) {
                return $existingToken;
            }
        }
        return null;
    }
}
