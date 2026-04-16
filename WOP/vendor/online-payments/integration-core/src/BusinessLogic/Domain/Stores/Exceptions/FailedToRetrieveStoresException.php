<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Stores\Exceptions;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
/**
 * Class FailedToRetrieveStoresException
 *
 * @package OnlinePayments\Core\BusinessLogic\Domain\Stores\Exceptions
 */
class FailedToRetrieveStoresException extends BaseTranslatableException
{
    public function __construct(Exception $previous)
    {
        parent::__construct(new TranslatableLabel('Failed to retrieve stores.', 'general.failedToRetrieveStores'), $previous);
    }
}
