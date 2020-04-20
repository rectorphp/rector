<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use function Amp\ByteStream\buffer;
use function Amp\call;
use Amp\Loop;
use Amp\Process\Process;
use function Amp\Promise\all;
use Generator;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ProcessCommand extends AbstractProcessCommand
{
    /**
     * @var int
     */
    private const MINIMUM_FILES_TO_PROCESS_IN_PARALLEL = 1;

    protected function configure(): void
    {
        parent::configure();

        $this->setAliases(['rectify']);

        $this->setDescription('Upgrade or refactor source code with provided rectors');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configuration->resolveFromInput($input);
        $this->configuration->setAreAnyPhpRectorsLoaded((bool) $this->rectorNodeTraverser->getPhpRectorCount());

        $this->rectorGuard->ensureSomeRectorsAreRegistered();
        $this->rectorGuard->ensureGetNodeTypesAreNodes();
        $this->stubLoader->loadStubs();

        $source = $this->resolvesSourcePaths($input);

        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles(
            $source,
            $this->configuration->getFileExtensions(),
            $this->configuration->mustMatchGitDiff()
        );

        $this->additionalAutoloader->autoloadWithInputAndSource($input, $source);

        $phpFileInfos = $this->processWithCache($phpFileInfos);

        $this->configuration->setFileInfos($phpFileInfos);

        if ($this->configuration->isParallelEnabled()) {
            return $this->processFilesInParallel($phpFileInfos);
        }

        if ($this->configuration->isCacheDebug()) {
            $this->symfonyStyle->note(sprintf('[cache] %d files after cache filter', count($phpFileInfos)));
            $this->symfonyStyle->listing($phpFileInfos);
        }

        $this->yamlProcessor->run($source);

        $this->rectorApplication->runOnFileInfos($phpFileInfos);

        $this->reportZeroCacheRectorsCondition();

        // report diffs and errors
        $outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        $outputFormatter->report($this->errorAndDiffCollector);

        $this->reportingExtensionRunner->run();

        // invalidate affected files
        $this->invalidateAffectedCacheFiles();

        // some errors were found → fail
        if ($this->errorAndDiffCollector->getErrors() !== []) {
            return ShellCode::SUCCESS;
        }

        // inverse error code for CI dry-run
        if ($this->configuration->isDryRun() && $this->errorAndDiffCollector->getFileDiffsCount()) {
            return ShellCode::ERROR;
        }

        return ShellCode::SUCCESS;
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     */
    private function processFilesInParallel(array $phpFileInfos): int
    {
        /** @var string[] $fileNames */
        $fileNames = array_map(static function (SmartFileInfo $smartFileInfo) {
            return $smartFileInfo->getRelativePathname();
        }, $phpFileInfos);

        // TODO: $maxFilesPerProcess

        $filesToChunkCount = (int) max(
            self::MINIMUM_FILES_TO_PROCESS_IN_PARALLEL,
            (int) (count($phpFileInfos) / $this->configuration->getParallelProcessesCount())
        );
        $chunkedFilenames = array_chunk($fileNames, $filesToChunkCount);

        $results = [];

        Loop::run(static function () use (&$results, $chunkedFilenames) {
            $promises = [];

            foreach ($chunkedFilenames as $filenamesForChildProcess) {
                $promises[] = call(static function () use ($filenamesForChildProcess): Generator {
                    $script = array_merge(
                        [
                            __DIR__ . '/../../../bin/rector', // Dynamic ?
                            CommandNaming::classToName(ProcessWorkerCommand::class),
                            // Please can we take somehow everything from configuration object or input object?
                            // etc $this->configuration->toCliArguments() ??
                            '--dry-run',
                            '--output-format', JsonOutputFormatter::NAME,
                        ],
                        $filenamesForChildProcess
                    );

                    $process = new Process($script);

                    yield $process->start();

                    return yield buffer($process->getStdout());
                });
            }

            $results = yield all($promises);
        });

        // Merge results from workers and act as normally

        // TODO: merge + deformat and pass it to output formatter
        // report diffs and errors

        print_r($results);

        return ShellCode::SUCCESS;
    }
}
