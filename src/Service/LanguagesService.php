<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Object\Language;
use EffectConnect\Marketplaces\Object\LanguagesCollection;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use EffectConnect\Marketplaces\Exception\SalesChannelNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class LanguagesService
 * @package EffectConnect\Marketplaces\Service
 */
class LanguagesService
{
    /**
     * @var SalesChannelService
     */
    protected $_salesChannelService;

    /**
     * @var SettingsService
     */
    protected $_settingsService;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_languageRepository;

    /**
     * @var SalesChannelEntity
     */
    private $_salesChannel;

    /**
     * @var SalesChannelContext
     */
    private $_salesChannelContext;

    /**
     * @var SettingStruct
     */
    protected $_settings;

    /**
     * @var LanguageCollection
     */
    protected $_systemLanguages;

    /**
     * LanguagesService constructor.
     *
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param EntityRepositoryInterface $languageRepository
     */
    public function __construct(
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        EntityRepositoryInterface $languageRepository
    ) {
        $this->_salesChannelService         = $salesChannelService;
        $this->_settingsService             = $settingsService;
        $this->_languageRepository          = $languageRepository;
    }

    /**
     * Get the languages for a specific sales channel.
     *
     * @param SalesChannelEntity $salesChannelEntity
     * @return LanguagesCollection
     * @throws SalesChannelNotFoundException
     */
    public function getLanguages(SalesChannelEntity $salesChannelEntity): LanguagesCollection
    {
        $this->_salesChannel            = $salesChannelEntity;
        $this->_salesChannelContext     = $this->_salesChannelService->getSalesChannelContext($salesChannelEntity->getId());
        $this->_settings                = $this->_settingsService->getSettings($salesChannelEntity->getId());
        $this->_systemLanguages         = $this->getSystemLanguages();

        $languages                      = $this->getSalesChannelLanguages();

        if ($this->_settings->isUseSystemLanguages()) {
            $languages                  = $this->_systemLanguages;
        }

        $fallbackLanguages              = [];
        $salesChannelDefaultLanguage    = $this->getSalesChannelDefaultLanguage();
        $systemDefaultLanguage          = $this->getSystemDefaultLanguage();

        if ($this->_settings->isUseFallbackTranslations()) {
            $useSalesChannelAsDefault   = $this->_settings->isUseSalesChannelDefaultLanguageAsFirstFallbackLanguage();
            $firstFallbackLanguage      = $useSalesChannelAsDefault && !is_null($salesChannelDefaultLanguage) ? $salesChannelDefaultLanguage : $systemDefaultLanguage;
            $secondFallbackLanguage     = $useSalesChannelAsDefault && !is_null($salesChannelDefaultLanguage) ? $systemDefaultLanguage : null;

            if (!is_null($firstFallbackLanguage)) {
                $fallbackLanguages[]    = $firstFallbackLanguage;
            }

            if (!is_null($secondFallbackLanguage)) {
                $fallbackLanguages[]    = $secondFallbackLanguage;
            }
        }

        return $this->generateCollection($languages, $fallbackLanguages, $salesChannelDefaultLanguage, $systemDefaultLanguage);
    }

    /**
     * Generate a LanguageCollection for the obtained languages.
     *
     * @param LanguageCollection $languages
     * @param LanguageEntity[] $fallbackLanguages
     * @return LanguagesCollection
     */
    protected function generateCollection(LanguageCollection $languages, array $fallbackLanguages = []): LanguagesCollection
    {
        $languageObjects                = [];
        $fallbackLanguageObjects        = [];

        foreach ($languages as $language) {
            $languageObject = $this->transformLanguage($language);

            if (!is_null($languageObject)) {
                $languageObjects[] = $languageObject;
            }
        }

        foreach ($fallbackLanguages as $language) {
            $fallbackLanguageObject = $this->transformLanguage($language);

            if (!is_null($fallbackLanguageObject)) {
                $fallbackLanguageObjects[] = $fallbackLanguageObject;
            }
        }

        return new LanguagesCollection($languageObjects, $fallbackLanguageObjects);
    }

    /**
     * Transform a LanguageEntity to a Language object.
     *
     * @param LanguageEntity|null $language
     * @return Language|null
     */
    protected function transformLanguage(?LanguageEntity $language = null): ?Language
    {
        if (is_null($language)) {
            return null;
        }

        $salesChannelDefaultLanguage    = $this->getSalesChannelDefaultLanguage();
        $systemDefaultLanguage          = $this->getSystemDefaultLanguage();

        $id             = $language->getId();
        $code           = $this->getLanguageCode($language);
        $inheritFrom    = null;

        if (is_null($code) || empty($code)) {
            return null;
        }

        if (!is_null($language->getParentId())) {
            $inheritFrom = $this->transformLanguage($this->getLanguageById($language->getParentId()));
        }

        return (new Language($id, $code, $inheritFrom))
            ->setIsSalesChannelDefault(!is_null($salesChannelDefaultLanguage) && $salesChannelDefaultLanguage->getId() === $id)
            ->setIsSystemDefault(!is_null($systemDefaultLanguage) && $systemDefaultLanguage->getId() === $id);
    }

    /**
     * Get all languages for the current Sales Channel.
     *
     * @return LanguageCollection
     */
    protected function getSalesChannelLanguages(): LanguageCollection
    {
        return $this->_salesChannel->getLanguages() ?? new LanguageCollection([]);
    }

    /**
     * Get the system languages.
     *
     * @return LanguageCollection
     */
    protected function getSystemLanguages(): LanguageCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociations([
            'translationCodeId',
            'locale',
            'parent',
            'parent.locale'
        ]);
        $searchResult = $this->_languageRepository->search($criteria, $this->_salesChannelContext->getContext());
        return LanguageCollection::createFrom($searchResult->getEntities());
    }

    /**
     * Get the Sales Channel default language.
     *
     * @return LanguageEntity|null
     */
    protected function getSalesChannelDefaultLanguage(): ?LanguageEntity
    {
        return $this->_salesChannel->getLanguage();
    }

    /**
     * Get the system default language.
     *
     * @return LanguageEntity|null
     */
    protected function getSystemDefaultLanguage(): ?LanguageEntity
    {
        $defaultLanguageId = $this->_salesChannelService->getDefaultContext()->getLanguageId();
        return $this->getLanguageById($defaultLanguageId);
    }

    /**
     * Get a language by ID.
     *
     * @param string $id
     * @return LanguageEntity|null
     */
    public function getLanguageById(string $id): ?LanguageEntity
    {
        if (is_null($this->_systemLanguages)) {
            $this->_systemLanguages = $this->getSystemLanguages();
        }

        foreach ($this->_systemLanguages as $language) {
            if ($language->getId() === $id) {
                return $language;
            }
        }

        return null;
    }

    /**
     * Get the language code from a language.
     *
     * @param LanguageEntity|null $language
     * @return string|null
     */
    public function getLanguageCode(?LanguageEntity $language): ?string
    {
        if (is_null($language) || is_null($language->getLocale())) {
            return null;
        }

        $languageCode = explode('-', $language->getLocale()->getCode())[0];

        if (empty($languageCode)) {
            return null;
        }

        return $languageCode;
    }
}