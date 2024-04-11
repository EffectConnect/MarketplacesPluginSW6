<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Helper\ExportsCleaner;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'ec:reset-tasks')]
class ResetFailedTasks extends Command
{
    /**
     * @inheritDoc
     */
    protected static $defaultName = 'ec:reset-tasks';

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([])
            ->setDescription('EffectConnect Marketplaces - Reset Tasks')
            ->setHelp("The <info>%command.name%</info> command can be used to reset the tasks for the EffectConnect Marketplaces plugin.")
        ;
    }

    /**
     * @var EntityRepository
     */
    protected $scheduledTaskRepository;

    public function __construct(EntityRepository $scheduledTaskRepository)
    {
        parent::__construct();
        $this->scheduledTaskRepository = $scheduledTaskRepository;
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Context|null $context
     */
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null): int
    {
        try {
            $updatePayload = [];
            $tasks = $this->scheduledTaskRepository->search(
                (new Criteria())->addFilter(new EqualsFilter('status', ScheduledTaskDefinition::STATUS_FAILED)),
                Context::createDefaultContext())
                ->getEntities();
            foreach ($tasks as $task) {
                $updatePayload[] = [
                    'id' => $task->getId(),
                    'status' => ScheduledTaskDefinition::STATUS_SCHEDULED,
                ];
            }
            if ($updatePayload) {
                $this->scheduledTaskRepository->update($updatePayload, Context::createDefaultContext());
            }
        } catch(\Throwable $e) {
            $output->writeln($this->generateOutputMessage(false, 'Failed: ' . $e->getMessage()));
            return 0;
        }
        $output->writeln($this->generateOutputMessage(true, 'Expiration export cleaned successfully.'));

        return 1;
    }

    /**
     * Generate a result message.
     *
     * @param bool $success
     * @param string $errorMessage
     * @return string
     */
    protected function generateOutputMessage(bool $success, string $errorMessage = '')
    {
        return sprintf(
                '<fg=%s>[%s]</>: <fg=cyan>[%s]</>',
                ($success ? 'green' : 'red'),
                ($success ? 'SUCCESS' : ' ERROR '),
                'Clean Exports'
            ) . (!empty($errorMessage) ? ': ' . $errorMessage : '');
    }
}