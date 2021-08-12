<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Transformer;

use EffectConnect\Marketplaces\Exception\CountryNotFoundException;
use EffectConnect\Marketplaces\Exception\CountryStateNotFoundException;
use EffectConnect\Marketplaces\Exception\CreateSalutationFailedException;
use EffectConnect\Marketplaces\Exception\InvalidAddressTypeException;
use EffectConnect\Marketplaces\Service\CustomFieldService;
use EffectConnect\PHPSdk\Core\Model\Response\BillingAddress;
use EffectConnect\PHPSdk\Core\Model\Response\ShippingAddress;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\Aggregate\CountryState\CountryStateEntity;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\Salutation\SalutationEntity;

/**
 * Class CustomerTransformerService
 * @package EffectConnect\Marketplaces\Service\Transformer
 */
class CustomerTransformerService
{
    /**
     * Possible male salutations.
     */
    protected const MALE_SALUTATIONS      = [
        'mr', 'dhr', 'sir', 'hr', 'sr', 'heer', 'meneer', 'm',
        'mr.', 'dhr.', 'sir.', 'hr.', 'sr.',
        'Mr', 'Dhr', 'Sir', 'Hr', 'Sr',
        'Mr.', 'Dhr.', 'Sir.', 'Hr.', 'Sr.', 'Heer', 'Meneer', 'M'
    ];

    /**
     * Possible male salutations.
     */
    protected const FEMALE_SALUTATIONS    = [
        'mrs', 'mevr', 'mvr', 'mw', 'mej', 'miss', 'ms', 'madam', 'madame', 'f',
        'mrs.', 'mevr.', 'mvr.', 'mw.', 'mej.', 'miss.', 'ms.',
        'Mrs', 'Mevr', 'Mvr', 'Mw', 'Mej', 'Miss', 'Ms',
        'Mrs.', 'Mevr.', 'Mvr.', 'Mw.', 'Mej.', 'Miss.', 'Ms.', 'Madam', 'Madame', 'F'
    ];

    /**
     * Possible unknown salutations.
     */
    protected const OTHER_SALUTATIONS     = [
        'not_specified', 'unknown', 'onbekend',
        'Not specified', 'Unknown', 'Onbekend'
    ];

    /**
     * @var EntityRepositoryInterface
     */
    protected $_salutationRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_countryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_countryStateRepository;

    /**
     * CustomerTransformerService constructor.
     *
     * @param EntityRepositoryInterface $salutationRepository
     * @param EntityRepositoryInterface $countryRepository
     * @param EntityRepositoryInterface $countryStateRepository
     */
    public function __construct(
        EntityRepositoryInterface $salutationRepository,
        EntityRepositoryInterface $countryRepository,
        EntityRepositoryInterface $countryStateRepository
    ) {
        $this->_salutationRepository    = $salutationRepository;
        $this->_countryRepository       = $countryRepository;
        $this->_countryStateRepository  = $countryStateRepository;
    }

    /**
     * Transform the order customer.
     *
     * @param BillingAddress $billingAddress
     * @param SalesChannelContext $context
     * @return array
     * @throws CreateSalutationFailedException
     */
    public function transformOrderCustomer(BillingAddress $billingAddress, SalesChannelContext $context): array
    {
        $customFields = [
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_CUSTOMER_PHONE_NUMBER    => $billingAddress->getPhone(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_CUSTOMER_TAX_NUMBER      => $billingAddress->getTaxNumber()
        ];

        $customFields[CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES]                   = $customFields;
        $customFields[CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_CUSTOMER]    = $customFields;

        return [
            'email'         => $billingAddress->getEmail(),
            'salutationId'  => $this->getSalutation($billingAddress->getSalutation(), $context)->getId(),
            'firstName'     => $billingAddress->getFirstName(),
            'lastName'      => $billingAddress->getLastName(),
            'company'       => $billingAddress->getCompany(),
            'customFields'  => $customFields,
            'tags'  => [
                [ 'name'    => 'EffectConnect' ]
            ]
        ];
    }

    /**
     * Transform one of the order addresses.
     *
     * @param string $id
     * @param BillingAddress|ShippingAddress $orderAddress
     * @param SalesChannelContext $context
     * @return array
     * @throws CountryNotFoundException
     * @throws CountryStateNotFoundException
     * @throws CreateSalutationFailedException
     * @throws InvalidAddressTypeException
     */
    public function transformOrderAddress(string $id, $orderAddress, SalesChannelContext $context): array
    {
        if (!is_object($orderAddress) || !(($orderAddress instanceof BillingAddress) || ($orderAddress instanceof ShippingAddress))) {
            throw new InvalidAddressTypeException();
        }

        $country    = $this->getCountry($orderAddress->getCountry(), $context);
        $state      = $this->getCountryState($orderAddress->getState(), $country->getId(), (boolval($country->get('forceStateInRegistration') ?? false)), $context);
        $address    = $orderAddress->getStreet() . ' ' . $orderAddress->getHouseNumber() . $orderAddress->getHouseNumberExtension();

        $customFields = [
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_ADDRESS_EMAIL        => $orderAddress->getEmail(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_ADDRESS_ADDRESS_NOTE => $orderAddress->getAddressNote(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_ADDRESS_STATE        => $orderAddress->getState()
        ];

        $customFields[CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES]               = $customFields;
        $customFields[CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_ADDRESS] = $customFields;

        return [
            'id'                        => $id,
            'countryId'                 => $country->getId(),
            'countryStateId'            => is_null($state) ? null : $state->getId(),
            'salutationId'              => $this->getSalutation($orderAddress->getSalutation(), $context)->getId(),
            'firstName'                 => $orderAddress->getFirstName(),
            'lastName'                  => $orderAddress->getLastName(),
            'street'                    => $address,
            'zipcode'                   => $orderAddress->getZipCode(),
            'city'                      => $orderAddress->getCity(),
            'company'                   => $orderAddress->getCompany(),
            'vatId'                     => $orderAddress->getTaxNumber(),
            'phoneNumber'               => $orderAddress->getPhone(),
            'additionalAddressLine1'    => $orderAddress->getAddressNote(),
            'customFields'              => $customFields
        ];
    }


    /**
     * Get the salutation object for the order.
     *
     * @param string $salutation
     * @param SalesChannelContext $context
     * @param bool $created
     * @return SalutationEntity
     * @throws CreateSalutationFailedException
     */
    protected function getSalutation(string $salutation, SalesChannelContext $context, bool $created = false): SalutationEntity
    {
        $salutations        = static::OTHER_SALUTATIONS;

        if ($salutation === 'm') {
            $salutations = static::MALE_SALUTATIONS;
        } elseif ($salutation === 'f') {
            $salutations = static::FEMALE_SALUTATIONS;
        }

        $criteria           = new Criteria();
        $keyFilter          = new EqualsAnyFilter('salutationKey', $salutations);
        $nameFilter         = new EqualsAnyFilter('displayName', $salutations);
        $multiFilter        = new MultiFilter(MultiFilter::CONNECTION_OR, [
            $keyFilter,
            $nameFilter
        ]);

        $criteria->addFilter($multiFilter);

        $result = $this->_salutationRepository->search($criteria, $context->getContext());

        if ($result->getTotal() > 0) {
            return $result->first();
        } elseif (!$created) {
            return $this->createSalutation($salutation, $salutations, $context);
        }

        throw new CreateSalutationFailedException($salutations[0]);
    }

    /**
     * Create a salutation when it does not exists yet.
     *
     * @param string $salutation
     * @param array $salutations
     * @param SalesChannelContext $context
     * @return SalutationEntity
     * @throws CreateSalutationFailedException
     */
    protected function createSalutation(string $salutation, array $salutations, SalesChannelContext $context): SalutationEntity
    {
        $this->_salutationRepository->create([
            [
                'id' => Uuid::randomHex(),
                'salutationKey' => $salutations[1],
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => $salutations[1],
                        'letterName' => $salutations[1],
                    ],
                ],
            ]
        ], $context->getContext());

        return $this->getSalutation($salutation, $context, true);
    }

    /**
     * Get the country object for the order.
     *
     * @param string $countryCode
     * @param SalesChannelContext $context
     * @return CountryEntity
     * @throws CountryNotFoundException
     */
    protected function getCountry(string $countryCode, SalesChannelContext $context): CountryEntity
    {
        $criteria   = new Criteria();
        $isoFilter  = new EqualsFilter('iso', strtoupper($countryCode));

        $criteria->addFilter($isoFilter);

        $result = $this->_countryRepository->search($criteria, $context->getContext());

        if ($result->getTotal() > 0) {
            return $result->first();
        }

        throw new CountryNotFoundException($countryCode);
    }

    /**
     * Get the country state object for the order (returns null when not found and not required).
     *
     * @param string $state
     * @param string $countryId
     * @param bool $required
     * @param SalesChannelContext $context
     * @return SalutationEntity|null
     * @throws CountryStateNotFoundException
     */
    protected function getCountryState(string $state, string $countryId, bool $required, SalesChannelContext $context): ?CountryStateEntity
    {
        if (empty($state)) {
            if ($required) {
                throw new CountryStateNotFoundException($state);
            } else {
                return null;
            }
        }

        $criteria           = new Criteria();
        $countryIdFilter    = new EqualsFilter('countryId', $countryId);
        $nameFilter         = new EqualsFilter('name', $state);
        $shortCodeFilter    = new ContainsFilter('shortCode', $state);
        $orMultiFilter      = new MultiFilter(MultiFilter::CONNECTION_OR, [
            $nameFilter,
            $shortCodeFilter
        ]);
        $andMultiFilter     = new MultiFilter(MultiFilter::CONNECTION_AND, [
            $countryIdFilter,
            $orMultiFilter
        ]);

        $criteria->addFilter($andMultiFilter);

        $result = $this->_countryStateRepository->search($criteria, $context->getContext());

        if ($result->getTotal() > 0) {
            return $result->first();
        }

        if ($required) {
            throw new CountryStateNotFoundException($state);
        } else {
            return null;
        }
    }
}