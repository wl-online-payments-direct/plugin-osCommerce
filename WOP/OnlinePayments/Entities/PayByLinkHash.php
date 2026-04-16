<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Entities;

use DateTime;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Configuration\IndexMap;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\ORM\Entity;
class PayByLinkHash extends Entity
{
    public const CLASS_NAME = __CLASS__;
    protected string $hash;
    protected string $orderId;
    protected DateTime $expiresAt;
    protected array $fields = ['id', 'hash', 'orderId'];
    public function getConfig(): EntityConfiguration
    {
        $map = new IndexMap();
        $map->addStringIndex('orderId');
        return new EntityConfiguration($map, 'PayByLinkHash');
    }
    public function inflate(array $data): void
    {
        $this->orderId = $data['orderId'] ?? '';
        $this->hash = $data['hash'] ?? '';
        $this->expiresAt = $data['expiresAt'] ? DateTime::createFromFormat('U', $data['expiresAt']) : NULL;
    }
    public function toArray(): array
    {
        return ['orderId' => $this->orderId, 'hash' => $this->hash, 'expiresAt' => $this->expiresAt ? $this->expiresAt->getTimestamp() : NULL];
    }
    public function getHash(): string
    {
        return $this->hash;
    }
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }
    public function getOrderId(): string
    {
        return $this->orderId;
    }
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }
    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }
    public function setExpiresAt(DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
}
