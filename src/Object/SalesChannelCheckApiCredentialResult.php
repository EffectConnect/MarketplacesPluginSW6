<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Object;

/**
 * Class SalesChannelCheckApiCredentialResult
 * @package EffectConnect\Marketplaces\Object
 */
class SalesChannelCheckApiCredentialResult
{
    /**
     * @var bool
     */
    protected $_valid;

    /**
     * @var string
     */
    protected $_message;

    /**
     * SalesChannelCheckApiCredentialResult constructor.
     *
     * @param bool $valid
     * @param string $message
     */
    public function __construct(bool $valid, string $message)
    {
        $this->_valid   = $valid;
        $this->_message = $message;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->_valid;
    }

    /**
     * @param bool $valid
     * @return SalesChannelCheckApiCredentialResult
     */
    public function setValid(bool $valid): self
    {
        $this->_valid = $valid;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->_message;
    }

    /**
     * @param string $message
     * @return SalesChannelCheckApiCredentialResult
     */
    public function setMessage(string $message): self
    {
        $this->_message = $message;
        return $this;
    }
}