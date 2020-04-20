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
            (int) ceil((count($phpFileInfos) / $this->configuration->getParallelProcessesCount()))
        );
        $chunkedFilenames = array_chunk($fileNames, $filesToChunkCount);

        $results = [];

        $this->symfonyStyle->note(sprintf('[debug] %d CPUs (ignored, just fyi)', $this->getCpuCount()));
        $this->symfonyStyle->note(sprintf('[debug] %d files to process', count($phpFileInfos)));
        $this->symfonyStyle->note(sprintf('[debug] running in %d processes', count($chunkedFilenames)));

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

    /**
     * Adapted from https://gist.github.com/divinity76/01ef9ca99c111565a72d3a8a6e42f7fb
     * returns number of cpu cores
     * Copyleft 2018, license: WTFPL
     * @throws \RuntimeException
     * @throws \LogicException
     * @return int
     * @psalm-suppress ForbiddenCode
     */
    private function getCpuCount(): int
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            /*
            $str = trim((string) shell_exec('wmic cpu get NumberOfCores 2>&1'));
            if (!preg_match('/(\d+)/', $str, $matches)) {
                throw new \RuntimeException('wmic failed to get number of cpu cores on windows!');
            }
            return ((int) $matches [1]);
            */
            return 1;
        }

        if (!extension_loaded('pcntl')) {
            return 1;
        }

        $has_nproc = trim((string) @shell_exec('command -v nproc'));
        if ($has_nproc) {
            $ret = @shell_exec('nproc');
            if (is_string($ret)) {
                $ret = trim($ret);
                /** @var int|false */
                $tmp = filter_var($ret, FILTER_VALIDATE_INT);
                if (is_int($tmp)) {
                    return $tmp;
                }
            }
        }

        $ret = @shell_exec('sysctl -n hw.ncpu');
        if (is_string($ret)) {
            $ret = trim($ret);
            /** @var int|false */
            $tmp = filter_var($ret, FILTER_VALIDATE_INT);
            if (is_int($tmp)) {
                return $tmp;
            }
        }

        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            $count = substr_count($cpuinfo, 'processor');
            if ($count > 0) {
                return $count;
            }
        }

        throw new \LogicException('failed to detect number of CPUs!');
    }
}
