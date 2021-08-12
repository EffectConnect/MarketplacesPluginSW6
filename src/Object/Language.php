<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Object;

/**
 * Class Language
 * @package EffectConnect\Marketplaces\Object
 */
class Language
{
    /**
     * @var string
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_code;

    /**
     * @var Language
     */
    protected $_inheritFrom;

    /**
     * @var bool
     */
    protected $_isSystemDefault;

    /**
     * @var bool
     */
    protected $_isSalesChannelDefault;

    /**
     * Language constructor.
     *
     * @param string $id
     * @param string $code
     * @param Language|null $inheritFrom
     */
    public function __construct(string $id, string $code, ?Language $inheritFrom = null)
    {
        $this->_id          = $id;
        $this->_code        = $code;
        $this->_inheritFrom = $inheritFrom;
    }

    /**
     * Get the ID for the language.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->_id;
    }

    /**
     * Get the language code for the language.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code;
    }

    /**
     * Get the language this language should inherit it's translations from.
     *
     * @return Language|null
     */
    public function getInheritFrom(): ?Language
    {
        return $this->_inheritFrom;
    }

    /**
     * Check if this language has an inherit language.
     *
     * @return bool
     */
    public function hasInheritFrom(): bool
    {
        return !is_null($this->_inheritFrom);
    }

    /**
     * Check if this language is the system default language.
     *
     * @return bool
     */
    public function isSystemDefault(): bool
    {
        return $this->_isSystemDefault;
    }

    /**
     * Check if this language is the sales channel default language.
     *
     * @return bool
     */
    public function isSalesChannelDefault(): bool
    {
        return $this->_isSalesChannelDefault;
    }

    /**
     * Set whether this language is the system's default language.
     *
     * @param bool $isSystemDefault
     * @return Language
     */
    public function setIsSystemDefault(bool $isSystemDefault): Language
    {
        $this->_isSystemDefault = $isSystemDefault;

        return $this;
    }

    /**
     * Set whether this language is the Sales Channel's default language.
     *
     * @param bool $isSalesChannelDefault
     * @return Language
     */
    public function setIsSalesChannelDefault(bool $isSalesChannelDefault): Language
    {
        $this->_isSalesChannelDefault = $isSalesChannelDefault;

        return $this;
    }
}