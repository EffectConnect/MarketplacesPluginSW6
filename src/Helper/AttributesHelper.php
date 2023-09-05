<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Object\LanguagesCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

/**
 * Class AttributesHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class AttributesHelper
{
    /**
     * @var LanguagesCollection
     */
    protected $_languages;

    /**
     * AttributesHelper Constructor
     *
     * @param LanguagesCollection $languages
     */
    public function __construct(LanguagesCollection $languages)
    {
        $this->_languages = $languages;
    }

    /**
     * Add XML translated values (used with the [values][value][index][names][name] array).
     *
     * @param array $valuesArray
     * @param Entity $object
     * @param string $property
     */
    public function setTranslatedXmlValues(array &$valuesArray, $object, string $property, $parent = null)
    {
        $translations = $this->_languages->getTranslations($object, $property, $parent);

        foreach ($translations as $languageCode => $translatedValue) {
            $this->addXmlValue($valuesArray, $translatedValue, $languageCode);
        }
    }

    /**
     * Get a static product attribute in an array format with the EffectConnect Marketplaces SDK expected values.
     *
     * @param string $name
     * @param $values
     * @return array|null
     */
    public function getStaticAttributeArray(string $name, $values, array $translations = null): ?array
    {
        if (is_null($name) || empty($name) || is_null($values) || is_object($values) || empty($values)) {
            return null;
        }

        if (!is_array($values)) {
            $values = [
                $values
            ];
        }

        $attributeArray = $this->getAttributeArrayFormat($this->getCodeFromString($name));

        foreach ($this->_languages->getLanguageCodes() as $languageCode) {
            if ($translations && isset($translations[$languageCode])) {
                $this->addXmlValue($attributeArray['names']['name'], $translations[$languageCode], $languageCode);
            } else {
                $this->addXmlValue($attributeArray['names']['name'], $name, $languageCode);
            }
        }

        foreach ($values as $value) {
            if (is_null($value) || empty($value)) {
                continue;
            }

            $valueArray = $this->getAttributeValueArrayFormat($this->getCodeFromString($value));

            foreach ($this->_languages->getLanguageCodes() as $languageCode) {
                $this->addXmlValue($valueArray['names']['name'], $value, $languageCode);
            }

            $attributeArray['values']['value'][] = $valueArray;
        }

        if (count($attributeArray['values']['value']) === 0) {
            return null;
        }

        return $attributeArray;
    }

    /**
     * Get a static translatable product attribute in an array format with the EffectConnect Marketplaces SDK expected values.
     *
     * @param string $name
     * @param Entity $object
     * @param string $property
     * @return array|null
     */
    public function getStaticTranslatableAttributeArray(string $name, $object, string $property): ?array
    {
        if (is_null($name) || empty($name) || is_null($property) || empty($property)) {
            return null;
        }

        $attributeArray                         = $this->getAttributeArrayFormat($this->getCodeFromString($name));
        $attributeArray['values']['value'][]    = $this->getAttributeValueArrayFormat(null);
        $translations                           = $this->_languages->getTranslations($object, $property);
        $defaultSystemLanguage                  = $this->_languages->getSystemDefaultLanguage();
        $defaultSystemLanguageInTranslations    = !is_null($defaultSystemLanguage) && array_key_exists($defaultSystemLanguage->getCode(), $translations);

        foreach ($this->_languages->getLanguages() as $language) {
            $this->addXmlValue($attributeArray['names']['name'], $name, $language->getCode());
        }

        foreach ($translations as $languageCode => $translatedValue) {
            if (
                is_null($attributeArray['values']['value'][0]['code']) || empty($attributeArray['values']['value'][0]['code']) && (
                ($defaultSystemLanguageInTranslations && $languageCode === $defaultSystemLanguage->getCode()) ||
                !$defaultSystemLanguageInTranslations
            )) {
                $attributeArray['values']['value'][0]['code'] = $this->getCodeFromString($translatedValue);
            }

            $this->addXmlValue($attributeArray['values']['value'][0]['names']['name'], $translatedValue, $languageCode);
        }

        if (
            count($attributeArray['names']['name']) === 0 ||
            count($attributeArray['values']['value'][0]['names']['name']) === 0 ||
            is_null($attributeArray['values']['value'][0]['code'])
        ) {
            return null;
        }

        return $attributeArray;
    }

    /**
     * Get a variable translatable product attribute in an array format with the EffectConnect Marketplaces SDK expected values.
     *
     * @param PropertyGroupOptionEntity $property
     * @return array|null
     */
    public function getVariableTranslatableAttributeArray(PropertyGroupOptionEntity $property): ?array
    {
        $attributeArray                             = $this->getAttributeArrayFormat($this->getCodeFromString(null));
        $attributeArray['values']['value'][]        = $this->getAttributeValueArrayFormat(null);

        if (is_null($property->getGroup())) {
            return null;
        }

        $this->setAttributeArray($attributeArray, $property->getGroup(), 'name');
        $this->setAttributeArray($attributeArray['values']['value'][0], $property, 'name');

        if (
            count($attributeArray['names']['name']) === 0 ||
            is_null($attributeArray['code']) ||
            count($attributeArray['values']['value'][0]['names']['name']) === 0 ||
            is_null($attributeArray['values']['value'][0]['code'])
        ) {
            return null;
        }

        return $attributeArray;
    }

    /**
     * Set ['names']['name'] translations to the name/value array.
     *
     * @param array $array
     * @param Entity $object
     * @param string $property
     */
    protected function setAttributeArray(array &$array, $object, string $property)
    {
        $translations                           = $this->_languages->getTranslations($object, $property);
        $defaultSystemLanguage                  = $this->_languages->getSystemDefaultLanguage();
        $defaultSystemLanguageInTranslations    = !is_null($defaultSystemLanguage) && array_key_exists($defaultSystemLanguage->getCode(), $translations);

        foreach ($translations as $languageCode => $translatedValue) {
            $codeIsNotSet                       = is_null($array['code']) || empty($array['code']);
            $defaultLanguagePresentAndCurrent   = $defaultSystemLanguageInTranslations && $languageCode === $defaultSystemLanguage->getCode();

            if ($codeIsNotSet && ($defaultLanguagePresentAndCurrent || !$defaultSystemLanguageInTranslations)) {
                $array['code'] = $this->getCodeFromString($translatedValue);
            }

            $this->addXmlValue($array['names']['name'], $translatedValue, $languageCode);
        }
    }

    /**
     * Merge attributes with the same code.
     *
     * @param array $attributes
     * @return array
     */
    public function mergeAttributes(array $attributes): array
    {
        $mergedAttributes = [];

        foreach ($attributes as $attribute) {
            if (!isset($mergedAttributes[$attribute['code']])) {
                $mergedAttributes[$attribute['code']] = $attribute;
            } else {
                foreach ($attribute['values']['value'] as $value) {
                    $exists = false;

                    foreach ($mergedAttributes[$attribute['code']]['values']['value'] as $mergedValue) {
                        if ($value['code'] === $mergedValue['code']) {
                            $exists = true;
                        }
                    }

                    if (!$exists) {
                        $mergedAttributes[$attribute['code']]['values']['value'][] = $value;
                    }
                }
            }
        }

        return array_values($mergedAttributes);
    }

    /**
     * Get the XML attribute array format (without values).
     *
     * @param string $code
     * @return array
     */
    protected function getAttributeArrayFormat(string $code = '') {
        return [
            'code'      => $code,
            'names'         => [
                'name'         => []
            ],
            'values'    => [
                'value'     => []
            ]
        ];
    }

    /**
     * Get the XML values array format.
     *
     * @param string|null $code
     * @return array
     */
    protected function getAttributeValueArrayFormat(?string $code = '') {
        return [
            'code'  => $code,
            'names' => [
                'name'  => []
            ]
        ];
    }

    /**
     * Add an XML prepared value (used with the [values][value][index][names][name] array).
     *
     * @param array $valuesArray
     * @param mixed $value
     * @param string $languageCode
     */
    public function addXmlValue(array &$valuesArray, $value, string $languageCode)
    {
        if (!is_null($value) && !empty($value) && !empty(trim(strip_tags(strval($value))))) {
            $valuesArray[] = [
                '_cdata'            => trim(strip_tags(strval($value))),
                '_attributes'       => [
                    'language'      => strval($languageCode)
                ]
            ];
        }
    }

    /**
     * Get the name/value code (snake-cased) from a string.
     *
     * @param $string
     * @return string
     */
    protected function getCodeFromString($string): string
    {
        return preg_replace('/[^a-z0-9]/i', '_', trim(strtolower(strip_tags(strval($string)))));
    }
}