<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Exception\CustomFieldsSetupException;
use EffectConnect\Marketplaces\Object\CustomFieldConfig;
use Exception;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomFieldService
 * @package EffectConnect\Marketplaces\Service
 */
class CustomFieldService
{
    // Custom field set global name.
    public const CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES                 = 'effectconnect_marketplaces';

    // Custom field set names.
    public const CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER           = 'effectconnect_marketplaces_order';
    public const CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM = 'effectconnect_marketplaces_order_line_item';
    public const CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_CUSTOMER  = 'effectconnect_marketplaces_order_customer';
    public const CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_ADDRESS   = 'effectconnect_marketplaces_order_address';

    // Custom fields for orders.
    public const CUSTOM_FIELD_KEY_ORDER_EFFECTCONNECT_ORDER_NUMBER              = 'effectConnectOrderNumber';
    public const CUSTOM_FIELD_KEY_ORDER_CHANNEL_ORDER_NUMBER                    = 'channelOrderNumber';
    public const CUSTOM_FIELD_KEY_ORDER_CHANNEL_ID                              = 'channelId';
    public const CUSTOM_FIELD_KEY_ORDER_CHANNEL_TYPE                            = 'channelType';
    public const CUSTOM_FIELD_KEY_ORDER_CHANNEL_SUBTYPE                         = 'channelSubtype';
    public const CUSTOM_FIELD_KEY_ORDER_CHANNEL_TITLE                           = 'channelTitle';
    public const CUSTOM_FIELD_KEY_ORDER_COMMISSION_FEE                          = 'commissionFee';
    public const CUSTOM_FIELD_KEY_ORDER_FULFILMENT_TYPE                         = 'fulfilmentType';

    // Custom fields for order line items.
    public const CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_EFFECTCONNECT_LINE_ID         = 'effectConnectLineId';
    public const CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_CHANNEL_LINE_ID               = 'channelLineId';
    public const CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_EFFECTCONNECT_ID              = 'effectConnectId';
    public const CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_CONNECTION_LINE_ID            = 'connectionLineId';
    public const CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_PRODUCT_ID                    = 'productId';

    // Custom fields for order customers.
    public const CUSTOM_FIELD_KEY_ORDER_CUSTOMER_PHONE_NUMBER                   = 'phoneNumber';
    public const CUSTOM_FIELD_KEY_ORDER_CUSTOMER_TAX_NUMBER                     = 'taxNumber';

    // Custom fields for order addresses.
    public const CUSTOM_FIELD_KEY_ORDER_ADDRESS_EMAIL                           = 'email';
    public const CUSTOM_FIELD_KEY_ORDER_ADDRESS_ADDRESS_NOTE                    = 'addressNote';
    public const CUSTOM_FIELD_KEY_ORDER_ADDRESS_STATE                           = 'state';

    /** @var ContainerInterface */
    private $_container;

    /** @var EntityRepositoryInterface */
    private $_customFieldSetRepository;

    /** @var EntityRepositoryInterface */
    private $_customFieldRepository;

    /** @var EntityRepositoryInterface */
    private $_customFieldSetRelationRepository;

    /**
     * CustomFieldService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->_container                           = $container;
        $this->_customFieldSetRepository            = $container->get('custom_field_set.repository');
        $this->_customFieldRepository               = $container->get('custom_field.repository');
        $this->_customFieldSetRelationRepository    = $container->get('custom_field_set_relation.repository');
    }

    /**
     * Create the defined custom fields (if they do not exist yet).
     *
     * @param Context $context
     * @throws CustomFieldsSetupException
     */
    public function createCustomFields(Context $context)
    {
        $fieldSetIds = $this->getOrCreateCustomFieldSetsIds($context);

        $this->createCustomFieldSetRelations($context, $fieldSetIds);

        foreach ($this->getCustomFieldsConfigsArray() as $customFieldConfig) {
            if (!isset($fieldSetIds[$customFieldConfig->getFieldsetKey()]) || is_null($fieldSetIds[$customFieldConfig->getFieldsetKey()])) {
                continue;
            }

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('name', $customFieldConfig->getKey()));
            $criteria->addFilter(new EqualsFilter('customFieldSetId', $fieldSetIds[$customFieldConfig->getFieldsetKey()]));

            $result = $this->_customFieldRepository->searchIds($criteria, $context);
            $id     = $result->firstId();

            if (is_null($id)) {
                $data = $customFieldConfig->generateCreationDataArray($fieldSetIds[$customFieldConfig->getFieldsetKey()]);
                try {
                    $this->_customFieldRepository->create([$data], $context);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }

    /**
     * Get the custom field sets IDs for EffectConnect Marketplaces.
     * If a fieldset does not exists, it will be created.
     *
     * @param Context $context
     * @return array
     */
    protected function getOrCreateCustomFieldSetsIds(Context $context)
    {
        $fields = [
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES                  => null,
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER            => null,
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM  => null,
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_CUSTOMER   => null,
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_ADDRESS    => null,
        ];

        foreach ($fields as $fieldKey => &$value) {
            $criteria = new Criteria();

            $criteria->addFilter(new EqualsFilter('name', $fieldKey));

            $result = $this->_customFieldSetRepository->searchIds($criteria, $context);
            $id     = $result->firstId();

            if (is_null($id)) {
                $id = Uuid::randomHex();

                try {
                    $this->_customFieldSetRepository->create([[
                        'id'        => $id,
                        'name'      => $fieldKey,
                        'config'    => [
                            'label'     => [
                                'en-US'     => 'EffectConnect Marketplaces',
                                'nl-NL'     => 'EffectConnect Marketplaces',
                                'de-DE'     => 'EffectConnect Marketplaces'
                            ]
                        ]
                    ]], $context);
                } catch (Exception $e) {
                    continue;
                }
            }

            $value = $id;
        }

        return $fields;
    }

    /**
     * Create the relations of the custom field sets with the corresponding entities.
     *
     * @param Context $context
     * @param array $customFieldSetIds
     */
    protected function createCustomFieldSetRelations(Context $context, array $customFieldSetIds)
    {
        $fields = [
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES                  => '',
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER            => OrderDefinition::ENTITY_NAME,
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM  => OrderLineItemDefinition::ENTITY_NAME,
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_CUSTOMER   => OrderCustomerDefinition::ENTITY_NAME,
            static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_ADDRESS    => OrderAddressDefinition::ENTITY_NAME
        ];

        foreach ($fields as $fieldKey => $entityName) {
            if (!isset($customFieldSetIds[$fieldKey]) || is_null($customFieldSetIds[$fieldKey])) {
                continue;
            }

            $criteria = new Criteria();

            $criteria->addFilter(new EqualsFilter('customFieldSetId', $customFieldSetIds[$fieldKey]));
            $criteria->addFilter(new EqualsFilter('entityName', $entityName));

            $result = $this->_customFieldSetRelationRepository->searchIds($criteria, $context);
            $id     = $result->firstId();

            if (is_null($id)) {
                $id = Uuid::randomHex();

                try {
                    $this->_customFieldSetRelationRepository->create([[
                        'id'                => $id,
                        'customFieldSetId'  => $customFieldSetIds[$fieldKey],
                        'entityName'        => $entityName
                    ]], $context);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }

    /**
     * Get the custom field configuration for all custom fields.
     *
     * @return CustomFieldConfig[]
     */
    protected function getCustomFieldsConfigsArray()
    {
        $customFieldsConfigs = [];

        // Order fields
        $customFieldsConfigs = array_merge($customFieldsConfigs, $this->getCustomOrderFields());

        // Orderline fields
        $customFieldsConfigs = array_merge($customFieldsConfigs, $this->getCustomOrderLineItemFields());

        // Customer fields
        $customFieldsConfigs = array_merge($customFieldsConfigs, $this->getCustomOrderCustomerFields());

        // Address fields
        $customFieldsConfigs = array_merge($customFieldsConfigs, $this->getCustomOrderAddressFields());

        return $customFieldsConfigs;
    }

    /**
     * Get the custom field configuration for all custom order fields.
     *
     * @return CustomFieldConfig[]
     */
    protected function getCustomOrderFields()
    {
        $customFieldsConfigs = [];

        // EffectConnect Order Number
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_EFFECTCONNECT_ORDER_NUMBER, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER))
            ->setPosition(10)
            ->setTranslatedLabel([
                'en-US' => 'EffectConnect Order Number',
                'nl-NL' => 'EffectConnect Ordernummer',
                'de-DE' => 'EffectConnect Bestellnummer',
        ]);

        // Channel Order Number
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_CHANNEL_ORDER_NUMBER, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER))
            ->setPosition(20)
            ->setTranslatedLabel([
                'en-US' => 'Channel Order Number',
                'nl-NL' => 'Kanaal Ordernummer',
                'de-DE' => 'Kanal Bestellnummer',
        ]);

        // Channel ID
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_CHANNEL_ID, CustomFieldTypes::INT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER))
            ->setPosition(30)
            ->setTranslatedLabel([
                'en-US' => 'Channel ID',
                'nl-NL' => 'Kanaal ID',
                'de-DE' => 'Kanal ID',
        ]);

        // Channel Type
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_CHANNEL_TYPE, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER))
            ->setPosition(40)
            ->setTranslatedLabel([
                'en-US' => 'Channel Type',
                'nl-NL' => 'Kanaal Type',
                'de-DE' => 'Kanal Art',
        ]);

        // Channel Subtype
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_CHANNEL_SUBTYPE, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER))
            ->setPosition(50)
            ->setTranslatedLabel([
                'en-US' => 'Channel Subtype',
                'nl-NL' => 'Kanaal Subtype',
                'de-DE' => 'Kanal Untertyp',
        ]);

        // Channel Title
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_CHANNEL_TITLE, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER))
            ->setPosition(60)
            ->setTranslatedLabel([
                'en-US' => 'Channel Title',
                'nl-NL' => 'Kanaal Titel',
                'de-DE' => 'Kanal Titel',
        ]);

        // Commission Fee
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_COMMISSION_FEE, CustomFieldTypes::FLOAT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER))
            ->setPosition(70)
            ->setTranslatedLabel([
                'en-US' => 'Commission Fee',
                'nl-NL' => 'Commissie',
                'de-DE' => 'Provisionsgebühr',
            ]);

        // FulfilmentType
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_FULFILMENT_TYPE, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER))
            ->setPosition(80)
            ->setTranslatedLabel([
                'en-US' => 'Fulfilment type',
                'nl-NL' => 'Fulfilmenttype',
                'de-DE' => 'Erfüllungstyp',
            ]);

        return $customFieldsConfigs;
    }

    /**
     * Get the custom field configuration for all custom orderline fields.
     *
     * @return CustomFieldConfig[]
     */
    protected function getCustomOrderLineItemFields()
    {
        $customFieldsConfigs = [];

        // EffectConnect Orderline ID
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_EFFECTCONNECT_LINE_ID, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM))
            ->setPosition(10)
            ->setTranslatedLabel([
                'en-US' => 'EffectConnect Orderline ID',
                'nl-NL' => 'EffectConnect Orderregel ID',
                'de-DE' => 'EffectConnect Bestellposten ID',
        ]);

        // Channel Orderline ID
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_CHANNEL_LINE_ID, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM))
            ->setPosition(20)
            ->setTranslatedLabel([
                'en-US' => 'Channel Orderline ID',
                'nl-NL' => 'Kanaal Orderregel ID',
                'de-DE' => 'Kanal Bestellposten ID',
        ]);

        // EffectConnect ID
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_EFFECTCONNECT_ID, CustomFieldTypes::INT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM))
            ->setPosition(30)
            ->setTranslatedLabel([
                'en-US' => 'EffectConnect ID',
                'nl-NL' => 'EffectConnect ID',
                'de-DE' => 'EffectConnect ID',
        ]);

        // Connection Orderline ID
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_CONNECTION_LINE_ID, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM))
            ->setPosition(40)
            ->setTranslatedLabel([
                'en-US' => 'Connection ID',
                'nl-NL' => 'Connectie ID',
                'de-DE' => 'Verbindung ID',
        ]);

        // Product ID
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_PRODUCT_ID, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM))
            ->setPosition(50)
            ->setTranslatedLabel([
                'en-US' => 'Product ID',
                'nl-NL' => 'Product ID',
                'de-DE' => 'Produkt ID',
        ]);

        return $customFieldsConfigs;
    }

    /**
     * Get the custom field configuration for all custom customer fields.
     *
     * @return CustomFieldConfig[]
     */
    protected function getCustomOrderCustomerFields()
    {
        $customFieldsConfigs = [];

        // Telephone Number
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_CUSTOMER_PHONE_NUMBER, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_CUSTOMER))
            ->setPosition(10)
            ->setTranslatedLabel([
                'en-US' => 'Telephone Number',
                'nl-NL' => 'Telefoonnummer',
                'de-DE' => 'Telefonnummer',
        ]);

        // VAT Number
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_CUSTOMER_TAX_NUMBER, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_CUSTOMER))
            ->setPosition(20)
            ->setTranslatedLabel([
                'en-US' => 'VAT Number',
                'nl-NL' => 'BTW-Nummer',
                'de-DE' => 'Umsatzsteuer-Identifikationsnummer',
        ]);

        return $customFieldsConfigs;
    }

    /**
     * Get the custom field configuration for all custom address fields.
     *
     * @return CustomFieldConfig[]
     */
    protected function getCustomOrderAddressFields()
    {
        $customFieldsConfigs = [];

        // Email Address
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_ADDRESS_EMAIL, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_ADDRESS))
            ->setPosition(10)
            ->setTranslatedLabel([
                'en-US' => 'Email Address',
                'nl-NL' => 'E-mailadres',
                'de-DE' => 'E-Mail-Addresse',
        ]);

        // Address Note
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_ADDRESS_ADDRESS_NOTE, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_ADDRESS))
            ->setPosition(20)
            ->setTranslatedLabel([
                'en-US' => 'Address Note',
                'nl-NL' => 'Address Notitie',
                'de-DE' => 'Adresshinweis',
        ]);

        // State
        $customFieldsConfigs[] = (new CustomFieldConfig(static::CUSTOM_FIELD_KEY_ORDER_ADDRESS_STATE, CustomFieldTypes::TEXT, static::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_ADDRESS))
            ->setPosition(30)
            ->setTranslatedLabel([
                'en-US' => 'State',
                'nl-NL' => 'Staat',
                'de-DE' => 'Staat',
        ]);

        return $customFieldsConfigs;
    }
}