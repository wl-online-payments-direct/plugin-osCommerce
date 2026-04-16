<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Repositories;

use common\modules\orderPayment\WOP\OnlinePayments\Models\ProductTypes;
/**
 * Class ProductTypesRepository.
 *
 * @package OnlinePayments\Repositories
 */
class ProductTypesRepository extends BaseRepositoryWithConditionalDelete
{
    public const THIS_CLASS_NAME = __CLASS__;
    protected function getDefaultModelClass(): string
    {
        return ProductTypes::class;
    }
}
