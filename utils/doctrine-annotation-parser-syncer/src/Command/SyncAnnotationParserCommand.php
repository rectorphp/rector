<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Command;

use Rector\Core\Configuration\Option;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\ClassSyncerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class SyncAnnotationParserCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ClassSyncerInterface[]
     */
    private $classSyncers = [];

    /**
     * @param ClassSyncerInterface[] $classSyncers
     */
    public function __construct(array $classSyncers, SymfonyStyle $symfonyStyle)
    {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->classSyncers = $classSyncers;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[DOC] Generate value-preserving DocParser from doctrine/annotation');

        $this->addOption(
            Option::OPTION_DRY_RUN,
            'n',
            InputOption::VALUE_NONE,
            'See diff of changes, do not save them to files.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption(Option::OPTION_DRY_RUN);

        foreach ($this->classSyncers as $classSyncer) {
            $isSuccess = $classSyncer->sync($dryRun);
            if (! $isSuccess) {
                $message = sprintf('File "%s" has changed, regenerate it: ', $classSyncer->getTargetFilePath());
                $this->symfonyStyle->error($message);

                return ShellCode::ERROR;
            }

            $message = sprintf(
                'Original "%s" was changed and refactored to "%s"',
                $classSyncer->getSourceFilePath(),
                $classSyncer->getTargetFilePath()
            );

            $this->symfonyStyle->note($message);
        }

        $this->symfonyStyle->success('Done');

        return ShellCode::SUCCESS;
    }
}
