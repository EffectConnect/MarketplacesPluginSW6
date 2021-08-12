<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Object;

use Exception;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

/**
 * Class LanguageCollection
 * @package EffectConnect\Marketplaces\Object
 */
class LanguagesCollection
{
    /**
     * @var Language[]
     */
    protected $_languages;

    /**
     * @var Language[]
     */
    protected $_fallbackLanguages;

    /**
     * LanguagesCollection constructor.
     *
     * @param array $languages
     * @param Language[] $fallbackLanguages
     */
    public function __construct(array $languages, array $fallbackLanguages = [])
    {
        $this->_languages           = $languages;
        $this->_fallbackLanguages   = $fallbackLanguages;
    }

    /**
     * Get the obtained languages.
     *
     * @return Language[]
     */
    public function getLanguages(): array
    {
        return $this->_languages;
    }

    /**
     * Get the obtained fallback languages.
     *
     * @return Language[]]
     */
    public function getFallbackLanguages(): array
    {
        return $this->_fallbackLanguages;
    }

    /**
     * Get the language codes of the obtained languages.
     *
     * @return string[]
     */
    public function getLanguageCodes(): array
    {
        return array_map(function (Language $language) {
            return $language->getCode();
        }, $this->_languages);
    }

    /**
     * Get the translations for a certain object's property.
     *
     * @param Entity $object
     * @param string $property
     * @return array
     */
    public function getTranslations($object, string $property): array
    {
        $translations = [];

        foreach ($this->_languages as $language) {
            try {
                $value = $this->getTranslation($object, $property, $language);
            } catch (Exception $e) {
                $value = null;
            }

            if (!is_null($value)) {
                $translations[$language->getCode()] = $value;
            }
        }

        return $translations;
    }

    /**
     * Get the translations for a certain object's property in a specific language.
     *
     * @param Entity $object
     * @param string $property
     * @param Language $language
     * @param bool $useFallbackLanguages
     * @return mixed
     */
    public function getTranslation($object, string $property, Language $language, bool $useFallbackLanguages = true)
    {
        if (is_null($object)) {
            return null;
        }

        /** @var TranslationEntity $translation */
        foreach ($object->getTranslations() ?? [] as $translation) {
            if ($language->getId() === $translation->getLanguageId() && $translation->has($property)) {
                $value = $translation->get($property);

                if (!is_null($value) && !empty($value) && !is_array($value) && !is_object($value)) {
                    return $value;
                }
            }
        }

        if ($language->hasInheritFrom()) {
            $value = $this->getTranslation($object, $property, $language->getInheritFrom(), false);

            if (!is_null($value)) {
                return $value;
            }
        }

        if ($useFallbackLanguages) {
            /** @var TranslationEntity $translation */
            foreach ($this->getFallbackLanguages() ?? [] as $language) {
                $value = $this->getTranslation($object, $property, $language, false);

                if (!is_null($value)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Get the system's default language.
     *
     * @return Language|null
     */
    public function getSystemDefaultLanguage(): ?Language
    {
        foreach ($this->_languages as $language) {
            if ($language->isSystemDefault()) {
                return $language;
            }
        }

        return null;
    }

    /**
     * Get the Sales Channel's default language.
     *
     * @return Language|null
     */
    public function getSalesChannelDefaultLanguage(): ?Language
    {
        foreach ($this->_languages as $language) {
            if ($language->isSalesChannelDefault()) {
                return $language;
            }
        }

        return null;
    }
}