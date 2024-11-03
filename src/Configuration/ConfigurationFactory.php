<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\ValueObject\Configuration;
use RectorPrefix202411\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202411\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @see \Rector\Tests\Configuration\ConfigurationFactoryTest
 */
final class ConfigurationFactory
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @api used in tests
     * @param string[] $paths
     */
    public function createForTests(array $paths) : Configuration
    {
        $fileExtensions = SimpleParameterProvider::provideArrayParameter(\Rector\Configuration\Option::FILE_EXTENSIONS);
        return new Configuration(\false, \true, \false, ConsoleOutputFormatter::NAME, $fileExtensions, $paths, \true, null, null, \false, null, \false, \false);
    }
    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function createFromInput(InputInterface $input) : Configuration
    {
        $isDryRun = (bool) $input->getOption(\Rector\Configuration\Option::DRY_RUN);
        $shouldClearCache = (bool) $input->getOption(\Rector\Configuration\Option::CLEAR_CACHE);
        $outputFormat = (string) $input->getOption(\Rector\Configuration\Option::OUTPUT_FORMAT);
        $showProgressBar = $this->shouldShowProgressBar($input, $outputFormat);
        $showDiffs = $this->shouldShowDiffs($input);
        $paths = $this->resolvePaths($input);
        $fileExtensions = SimpleParameterProvider::provideArrayParameter(\Rector\Configuration\Option::FILE_EXTENSIONS);
        $isParallel = SimpleParameterProvider::provideBoolParameter(\Rector\Configuration\Option::PARALLEL);
        $parallelPort = (string) $input->getOption(\Rector\Configuration\Option::PARALLEL_PORT);
        $parallelIdentifier = (string) $input->getOption(\Rector\Configuration\Option::PARALLEL_IDENTIFIER);
        $isDebug = (bool) $input->getOption(\Rector\Configuration\Option::DEBUG);
        // using debug disables parallel, so emitting exception is straightforward and easier to debug
        if ($isDebug) {
            $isParallel = \false;
        }
        $memoryLimit = $this->resolveMemoryLimit($input);
        $isReportingWithRealPath = SimpleParameterProvider::provideBoolParameter(\Rector\Configuration\Option::ABSOLUTE_FILE_PATH);
        return new Configuration($isDryRun, $showProgressBar, $shouldClearCache, $outputFormat, $fileExtensions, $paths, $showDiffs, $parallelPort, $parallelIdentifier, $isParallel, $memoryLimit, $isDebug, $isReportingWithRealPath);
    }
    private function shouldShowProgressBar(InputInterface $input, string $outputFormat) : bool
    {
        $noProgressBar = (bool) $input->getOption(\Rector\Configuration\Option::NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return \false;
        }
        if ($this->symfonyStyle->isVerbose()) {
            return \false;
        }
        return $outputFormat === ConsoleOutputFormatter::NAME;
    }
    private function shouldShowDiffs(InputInterface $input) : bool
    {
        $noDiffs = (bool) $input->getOption(\Rector\Configuration\Option::NO_DIFFS);
        if ($noDiffs) {
            return \false;
        }
        // fallback to parameter
        return !SimpleParameterProvider::provideBoolParameter(\Rector\Configuration\Option::NO_DIFFS, \false);
    }
    /**
     * @return string[]|mixed[]
     */
    private function resolvePaths(InputInterface $input) : array
    {
        $commandLinePaths = (array) $input->getArgument(\Rector\Configuration\Option::SOURCE);
        // give priority to command line
        if ($commandLinePaths !== []) {
            return $commandLinePaths;
        }
        // fallback to parameter
        return SimpleParameterProvider::provideArrayParameter(\Rector\Configuration\Option::PATHS);
    }
    private function resolveMemoryLimit(InputInterface $input) : ?string
    {
        $memoryLimit = $input->getOption(\Rector\Configuration\Option::MEMORY_LIMIT);
        if ($memoryLimit !== null) {
            return (string) $memoryLimit;
        }
        if (!SimpleParameterProvider::hasParameter(\Rector\Configuration\Option::MEMORY_LIMIT)) {
            return null;
        }
        return SimpleParameterProvider::provideStringParameter(\Rector\Configuration\Option::MEMORY_LIMIT);
    }
}
