<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Monolog\Logger;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

/**
 * Class AbstractTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
abstract class AbstractTaskHandler extends ScheduledTaskHandler
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
     * @var LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * AbstractTaskHandler constructor.
     *
     * @param EntityRepositoryInterface $scheduledTaskRepository
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->_salesChannelService = $salesChannelService;
        $this->_settingsService     = $settingsService;
        $this->_loggerFactory       = $loggerFactory;
    }
}