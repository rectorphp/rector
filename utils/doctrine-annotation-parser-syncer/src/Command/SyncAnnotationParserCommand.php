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
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

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
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @param ClassSyncerInterface[] $classSyncers
     */
    public function __construct(array $classSyncers, SymfonyStyle $symfonyStyle, ParameterProvider $parameterProvider)
    {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->classSyncers = $classSyncers;
        $this->parameterProvider = $parameterProvider;
    }

    protected function configure(): void
    {
        // disable imports
        $this->parameterProvider->changeParameter(Option::AUTO_IMPORT_NAMES, false);

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
                $sourceFileInfo = new SmartFileInfo($classSyncer->getSourceFilePath());

                $message = sprintf(
                    'File "%s" has changed,%sregenerate it: %s',
                    $sourceFileInfo->getRelativeFilePathFromCwd(),
                    PHP_EOL,
                    'bin/rector sync-annotation-parser'
                );
                $this->symfonyStyle->error($message);

                return ShellCode::ERROR;
            }

            $message = $this->createMessageAboutFileChanges($classSyncer, $dryRun);

            $this->symfonyStyle->note($message);
        }

        $this->symfonyStyle->success('Done');

        return ShellCode::SUCCESS;
    }

    private function createMessageAboutFileChanges(ClassSyncerInterface $classSyncer, bool $dryRun): string
    {
        $sourceFileInfo = new SmartFileInfo($classSyncer->getSourceFilePath());
        $targetFileInfo = new SmartFileInfo($classSyncer->getTargetFilePath());

        $messageFormat = $dryRun ? 'Original "%s" is in sync with "%s"' : 'Original "%s" was changed and refactored to "%s"';

        return sprintf(
            $messageFormat,
            $sourceFileInfo->getRelativeFilePathFromCwd(),
            $targetFileInfo->getRelativeFilePathFromCwd()
        );
    }
}
