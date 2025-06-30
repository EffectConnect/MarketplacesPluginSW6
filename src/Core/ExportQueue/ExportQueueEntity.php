<?php

namespace EffectConnect\Marketplaces\Core\ExportQueue;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ExportQueueEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string|null
     */
    protected $salesChannelId;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $status;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ExportQueueEntity
     */
    public function setId(string $id): ExportQueueEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ExportQueueEntity
     */
    public function setType(string $type): ExportQueueEntity
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return ExportQueueEntity
     */
    public function setIdentifier(string $identifier): ExportQueueEntity
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string|null $salesChannelId
     * @return ExportQueueEntity
     */
    public function setSalesChannelId(?string $salesChannelId): ExportQueueEntity
    {
        $this->salesChannelId = $salesChannelId;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return ExportQueueEntity
     */
    public function setData(array $data): ExportQueueEntity
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ExportQueueEntity
     */
    public function setStatus(string $status): ExportQueueEntity
    {
        $this->status = $status;
        return $this;
    }

}