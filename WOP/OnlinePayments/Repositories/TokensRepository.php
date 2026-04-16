<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Models\Tokens;
/**
 * Class TokensRepository.
 *
 * @package OnlinePayments\Repositories
 */
class TokensRepository extends BaseRepositoryWithConditionalDelete
{
    public const THIS_CLASS_NAME = __CLASS__;
    protected function getDefaultModelClass(): string
    {
        return Tokens::class;
    }
}
