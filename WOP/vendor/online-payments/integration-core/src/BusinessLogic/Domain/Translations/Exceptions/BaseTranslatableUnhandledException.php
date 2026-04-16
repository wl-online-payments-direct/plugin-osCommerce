<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Translations\Exceptions;

use Throwable;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
/**
 * Class BaseTranslatableUnhandledException
 *
 * @package OnlinePayments\Core\BusinessLogic\Domain\Translations\Model
 */
class BaseTranslatableUnhandledException extends BaseTranslatableException
{
    /**
     * @param Throwable $previous
     */
    public function __construct(Throwable $previous)
    {
        parent::__construct(new TranslatableLabel('Unhandled error occurred: ' . $previous->getMessage(), 'general.unhandled'), $previous);
    }
}
