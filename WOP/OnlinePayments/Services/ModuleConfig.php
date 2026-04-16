<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Services;

/**
 * Class ModuleConfig.
 *
 * @package OnlinePayments\Services
 */
class ModuleConfig
{
    /**
     * @var string[]
     */
    private $config;
    public function __construct(string $moduleConfigFile)
    {
        if (!file_exists($moduleConfigFile)) {
            throw new \InvalidArgumentException("Module config file {$moduleConfigFile} does not exist");
        }
        $this->config = json_decode(file_get_contents($moduleConfigFile), \true);
    }
    public function getBrand(): string
    {
        return $this->config['BRAND'];
    }
    public function getModuleName(): string
    {
        return $this->config['MODULE_NAME'];
    }
    public function getName(): string
    {
        return $this->config['NAME'];
    }
    public function getDescription(): string
    {
        return $this->config['DESCRIPTION'];
    }
    public function getHelpLink(): string
    {
        return $this->config['HELP_LINK'];
    }
    public function getVersion(): string
    {
        return $this->config['VERSION'];
    }
    public function getGitHubEndpoint(): string
    {
        return $this->config['GITHUB_ENDPOINT'];
    }
}
