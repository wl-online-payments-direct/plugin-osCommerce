<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services\Integration;

use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Integration\Version\VersionService as CoreVersionService;
use common\modules\orderPayment\WOP\OnlinePayments\Core\BusinessLogic\Domain\Version\VersionInfo;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Proxies\GithubProxy;
class VersionService implements CoreVersionService
{
    private GithubProxy $proxy;
    /**
     * @param GithubProxy $proxy
     */
    public function __construct(GithubProxy $proxy)
    {
        $this->proxy = $proxy;
    }
    /**
     * @inheritDoc
     */
    public function getVersionInfo(): VersionInfo
    {
        $current = ModuleHelper::getModuleConfig()->getVersion();
        try {
            $latest = $this->proxy->getLatestVersion();
        } catch (\Exception $e) {
            $latest = $current;
        }
        return new VersionInfo($current, $latest);
    }
}
