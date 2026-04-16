<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Aspects;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\ApiFacades\Response\TranslatableErrorResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Bootstrap\Aspect\Aspect;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Translations\Exceptions\BaseTranslatableUnhandledException;
use Throwable;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Logger\Logger;
/**
 * Class ErrorHandlingAspect.
 *
 * @package OnlinePayments\Core\Bootstrap\ApiFacades\Aspects
 */
class ErrorHandlingAspect implements Aspect
{
    /**
     * @throws Exception
     */
    public function applyOn($callee, array $params = [])
    {
        try {
            $response = call_user_func_array($callee, $params);
        } catch (BaseTranslatableException $e) {
            Logger::logError($e->getMessage(), 'Core', ['message' => $e->getMessage(), 'type' => get_class($e), 'trace' => $e->getTraceAsString()]);
            $response = TranslatableErrorResponse::fromError($e);
        } catch (Throwable $e) {
            Logger::logError('Unhandled error occurred.', 'Core', ['message' => $e->getMessage(), 'type' => get_class($e), 'trace' => $e->getTraceAsString()]);
            $exception = new BaseTranslatableUnhandledException($e);
            $response = TranslatableErrorResponse::fromError($exception);
        }
        return $response;
    }
}
