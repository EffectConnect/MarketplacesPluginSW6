<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\AbstractTask;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Monolog\Logger;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Throwable;

/**
 * Class AbstractTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
abstract class AbstractTaskHandler extends ScheduledTaskHandler
{
    protected const LOGGER_PROCESS = LoggerProcess::OTHER;

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
     * @var AbstractTask
     */
    protected $_task;

    /**
     * AbstractTaskHandler constructor.
     *
     * @param EntityRepository $scheduledTaskRepository
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->_salesChannelService = $salesChannelService;
        $this->_settingsService     = $settingsService;
        $this->_loggerFactory       = $loggerFactory;
        $this->_logger              = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    protected abstract function runTask(): void;

    public function run(): void
    {
        try {
            $this->_logger->info('Executing task handler '.self::class.' started.', [
                'process'       => static::LOGGER_PROCESS
            ]);

            $this->runTask();
        } catch (\Throwable $e) {
            $this->_logger->critical('Executing task handler '.self::class.' failed (unknown error)', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
            ]);
        }
        $this->_logger->info('Executing task handler '.self::class.' ended.', [
            'process'       => static::LOGGER_PROCESS
        ]);
    }

    /**
     * @throws Throwable
     */
    public function handle($task): void
    {
        $this->_task = $task;
        parent::handle($task);
    }

}