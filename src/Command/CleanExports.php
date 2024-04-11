<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Helper\ExportsCleaner;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanExports
 * @package EffectConnect\Marketplaces\Command
 */
#[AsCommand(name: 'ec:clean-exports')]
class CleanExports extends Command
{
    /**
     * @inheritDoc
     */
    protected static $defaultName = 'ec:clean-exports';

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([])
            ->setDescription('EffectConnect Marketplaces - Clean Exports')
            ->setHelp("The <info>%command.name%</info> command can be used to clean the exports for the EffectConnect Marketplaces plugin.")
            ->addArgument('expiration-days', InputArgument::OPTIONAL, 'Determines after how many days a export item expires and is deleted (optional - default is ' . ExportsCleaner::EXPORT_EXPIRATION_DAYS . ' days).');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Context|null $context
     */
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null): int
    {
        $expirationDaysString = $input->getArgument('expiration-days') ?? ExportsCleaner::EXPORT_EXPIRATION_DAYS;

        if (!is_numeric($expirationDaysString)) {
            $output->writeln($this->generateOutputMessage(false, 'Expiration days value is not valid (only numeric) value "' . $expirationDaysString . '".'));
        }

        $expirationDays = intval($input->getArgument('expiration-days') ?? ExportsCleaner::EXPORT_EXPIRATION_DAYS);

        ExportsCleaner::cleanExports($expirationDays);

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