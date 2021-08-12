<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Object;

use EffectConnect\Marketplaces\Service\CustomFieldService;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class CustomFieldConfig
 * @package EffectConnect\Marketplaces\Object
 */
class CustomFieldConfig
{
    /**
     * @var string
     */
    protected $_key;

    /**
     * @var array
     * Use the locale as key and the translations as value.
     */
    protected $_translatedLabel = [];

    /**
     * @var string
     * Use \Shopware\Core\System\CustomField\CustomFieldTypes constants.
     */
    protected $_customFieldType;

    /**
     * @var string
     */
    protected $_componentName = 'sw-field';

    /**
     * @var int
     */
    protected $_position = 1;

    /**
     * @var bool
     */
    protected $_active = true;

    /**
     * @var string|null
     */
    protected $_fieldsetKey = null;

    /**
     * Language constructor.
     *
     * @param string $key
     * @param string $customFieldType
     * @param string|null $fieldsetKey
     * @param bool $active
     */
    public function __construct(string $key, string $customFieldType, string $fieldsetKey = null, bool $active = true)
    {
        $this->_key             = $key;
        $this->_customFieldType = $customFieldType;
        $this->_fieldsetKey     = $fieldsetKey;
        $this->_active          = $active;
    }

    /**
     * Get the key for the custom field.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->_key;
    }

    /**
     * Get the translated label array for the custom field.
     * Use the locale as key and the translations as value.
     *
     * @return array
     */
    public function getTranslatedLabel(): array
    {
        return $this->_translatedLabel;
    }

    /**
     * Get the custom field type for the custom field.
     * Use \Shopware\Core\System\CustomField\CustomFieldTypes constants.
     *
     * @return string
     */
    public function getCustomFieldType(): string
    {
        return $this->_customFieldType;
    }

    /**
     * Get the component name for the custom field.
     *
     * @return string
     */
    public function getComponentName(): string
    {
        return $this->_componentName;
    }

    /**
     * Get the position for the custom field.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->_position;
    }

    /**
     * Get whether the custom field is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->_active;
    }

    /**
     * Get the fieldset key for the custom field.
     *
     * @return string
     */
    public function getFieldsetKey() : string
    {
        return $this->_fieldsetKey ?? CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES;
    }

    /**
     * Set the key for the custom field.
     *
     * @param string $key
     * @return static|self
     */
    public function setKey(string $key): self
    {
        $this->_key = $key;
        return $this;
    }

    /**
     * Set the translated label array for the custom field.
     * Use the locale as key and the translations as value.
     *
     * @param array $translatedLabel
     * @return static|self
     */
    public function setTranslatedLabel(array $translatedLabel): self
    {
        $this->_translatedLabel = $translatedLabel;
        return $this;
    }

    /**
     * Set the custom field type for the custom field.
     * Use \Shopware\Core\System\CustomField\CustomFieldTypes constants.
     *
     * @param string $customFieldType
     * @return static|self
     */
    public function setCustomFieldType(string $customFieldType): self
    {
        $this->_customFieldType = $customFieldType;
        return $this;
    }

    /**
     * Set the component name for the custom field.
     *
     * @param string $componentName
     * @return static|self
     */
    public function setComponentName(string $componentName): self
    {
        $this->_componentName = $componentName;
        return $this;
    }

    /**
     * Set the position for the custom field.
     *
     * @param int $position
     * @return static|self
     */
    public function setPosition(int $position): self
    {
        $this->_position = $position;
        return $this;
    }

    /**
     * Set whether the custom field is active.
     *
     * @param bool $active
     * @return static|self
     */
    public function setActive(bool $active): self
    {
        $this->_active = $active;
        return $this;
    }

    /**
     * Set the fieldset key for the custom field.
     *
     * @param string $fieldsetKey
     * @return static|self
     */
    public function setFieldsetKey(string $fieldsetKey): self
    {
        $this->_fieldsetKey = $fieldsetKey;
        return $this;
    }

    /**
     * Add a label translation.
     *
     * @param string $locale
     * @param string $translation
     * @return static|self
     */
    public function addLabelTranslation(string $locale, string $translation): self
    {
        $this->_translatedLabel[$locale] = $translation;
        return $this;
    }

    /**
     * Remove a label translation.
     *
     * @param string $locale
     * @return static|self
     */
    public function removeLabelTranslation(string $locale): self
    {
        if (isset($this->_translatedLabel[$locale])) {
            unset($this->_translatedLabel[$locale]);
        }

        return $this;
    }

    /**
     * Generate the creation data which can be used to create the custom field through a repository.
     *
     * @param string $setId
     * @param string|null $id
     * @return array
     */
    public function generateCreationDataArray(string $setId, string $id = null): array
    {
        $id = is_null($id) ? Uuid::randomHex() : $id;

        return [
            'id'                => $id,
            'name'              => $this->getKey(),
            'type'              => $this->getCustomFieldType(),
            'customFieldSetId'  => $setId,
            'active'            => $this->isActive(),
            'config'            => [
                'componentName'         => $this->getComponentName(),
                'customFieldType'       => $this->getCustomFieldType(),
                'customFieldPosition'   => $this->getPosition(),
                'label'                 => $this->getTranslatedLabel()
            ]
        ];
    }
}