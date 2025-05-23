<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service;

use Exception;
use EffectConnect\Marketplaces\Exception\SalesChannelNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class SalesChannelService
 * @package EffectConnect\Marketplaces\Service
 */
class SalesChannelService
{
    /**
     * @var EntityRepository
     */
    protected $_salesChannelRepository;

    /**
     * @var SalesChannelContextFactory
     */
    protected $_salesChannelContextFactory;

    /**
     * These associations are needed when obtaining sales channels for the purpose of the plug-in's logic.
     */
    protected const SALES_CHANNEL_ASSOCIATIONS = [
        'language',
        'language.locale',
        'language.parent',
        'language.parent.locale',
        'languages',
        'languages.locale',
        'languages.parent',
        'languages.parent.locale',
        'domains'
    ];

    /**
     * SalesChannelService constructor.
     *
     * @param EntityRepository $salesChannelRepository
     * @param SalesChannelContextFactory $salesChannelContextFactory
     */
    public function __construct(
        EntityRepository $salesChannelRepository,
        $salesChannelContextFactory // removed type because of Shopware 6.4 compatibility.
    ) {
        $this->_salesChannelRepository      = $salesChannelRepository;
        $this->_salesChannelContextFactory  = $salesChannelContextFactory;
    }

    /**
     * Get the SalesChannelEntity for a specific sales channel ID.
     *
     * @param string $salesChannelId
     * @param Context|null $context
     * @return SalesChannelEntity
     * @throws SalesChannelNotFoundException
     */
    public function getSalesChannel(string $salesChannelId, Context $context = null): SalesChannelEntity
    {
        if (is_null($context)) {
            $context = $this->getContext($salesChannelId);
        }

        $criteria = new Criteria([$salesChannelId]);

        $criteria->addAssociations(self::SALES_CHANNEL_ASSOCIATIONS);

        $salesChannels = $this->_salesChannelRepository->search($criteria, $context);

        if ($salesChannels->count() <= 0) {
            throw new SalesChannelNotFoundException($salesChannelId);
        }

        return $salesChannels->first();
    }

    /**
     * Get an array with SalesChannelEntity objects for all sales channels.
     *
     * @param Context|null $context
     * @return SalesChannelEntity[]
     */
    public function getSalesChannels(?Context $context = null): array
    {
        if (is_null($context)) {
            try {
                $context = $this->getContext();
            } catch (SalesChannelNotFoundException $e) {
                $context = $this->getDefaultContext();
            }
        }

        $criteria = new Criteria();

        $criteria->addAssociations(self::SALES_CHANNEL_ASSOCIATIONS);

        $salesChannels      = $this->_salesChannelRepository->search($criteria, $context);

        return $salesChannels->getEntities()->getElements();
    }

    /**
     * Check if a sales channel exists.
     *
     * @param string $salesChannelId
     * @return bool
     */
    public function salesChannelExists(string $salesChannelId): bool
    {
        $context        = $this->getDefaultContext();
        $salesChannels  = $this->_salesChannelRepository->search(new Criteria([$salesChannelId]), $context);
        return $salesChannels->count() > 0;
    }

    /**
     * Obtain the default context.
     *
     * @return Context
     */
    public function getDefaultContext(): Context
    {
        return Context::createDefaultContext();
    }

    /**
     * Get the sales channel context for a specific sales channel.
     *
     * @param string $salesChannelId
     * @return SalesChannelContext
     * @throws SalesChannelNotFoundException
     */
    public function getSalesChannelContext(string $salesChannelId): SalesChannelContext
    {
        try {
            if (!$this->salesChannelExists($salesChannelId)) {
                throw new SalesChannelNotFoundException($salesChannelId);
            }

            $hex = Uuid::randomHex();
            return $this->_salesChannelContextFactory->create($hex, $salesChannelId);
        } catch (Exception $exception) {
            throw new SalesChannelNotFoundException($salesChannelId);
        }
    }

    /**
     * Get the context for a specific sales channel, or the default one when no sales channel id is set.
     *
     * @param string|null $salesChannelId
     * @return Context
     * @throws SalesChannelNotFoundException
     */
    public function getContext(?string $salesChannelId = null): Context
    {
        $context = null;

        if (!is_null($salesChannelId)) {
            try {
                $salesChannelContext = $this->getSalesChannelContext($salesChannelId);
                $context = $salesChannelContext->getContext();
            } catch (Exception $exception) {
                throw new SalesChannelNotFoundException($salesChannelId);
            }
        }

        if (is_null($context)) {
            $context = $this->getDefaultContext();
        }

        return $context;
    }
}