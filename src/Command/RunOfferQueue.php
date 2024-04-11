<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Service\OfferQueueService;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunOfferQueue
 * @package EffectConnect\Marketplaces\Command
 */
#[AsCommand(name: 'ec:run-offer-queue')]
class RunOfferQueue extends Command
{
    /**
     * @inheritDoc
     */
    protected static $defaultName = 'ec:run-offer-queue';

    /**
     * @var OfferQueueService
     */
    private $queueService;

    public function __construct(OfferQueueService $queueService, string $name = null)
    {
        parent::__construct($name);
        $this->queueService = $queueService;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([])
            ->setDescription('EffectConnect Marketplaces - Run Offer Queue')
            ->setHelp("The <info>%command.name%</info> command can be used to send queued offer changes to EffectConnect.")
        ;
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Context|null $context
     */
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null): int
    {
        $this->queueService->run();
        return 1;
    }
}