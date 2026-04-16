<?php

/*
 * This file was automatically generated.
 */
namespace common\modules\orderPayment\WOP\OnlinePayments\Sdk;

use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Logging\CommunicatorLogger;
use common\modules\orderPayment\WOP\OnlinePayments\Sdk\Merchant\MerchantClientInterface;
/**
 * Payment platform client interface.
 */
interface ClientInterface
{
    /**
     * @param CommunicatorLogger $communicatorLogger
     */
    function enableLogging(CommunicatorLogger $communicatorLogger);
    /**
     * @return void
     */
    function disableLogging();
    /**
     * @param string $clientMetaInfo
     * @return $this
     */
    function setClientMetaInfo($clientMetaInfo);
    /**
     * Resource /v2/{merchantId}
     *
     * @param string $merchantId
     * @return MerchantClientInterface
     */
    function merchant($merchantId);
}
