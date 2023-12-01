<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\ValueObject\Configuration;
use RectorPrefix202312\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202312\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @see \Rector\Core\Tests\Configuration\ConfigurationFactoryTest
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
        $fileExtensions = SimpleParameterProvider::provideArrayParameter(\Rector\Core\Configuration\Option::FILE_EXTENSIONS);
        $isCollectors = SimpleParameterProvider::provideBoolParameter(\Rector\Core\Configuration\Option::COLLECTORS, \false);
        return new Configuration(\false, \true, \false, ConsoleOutputFormatter::NAME, $fileExtensions, $paths, \true, null, null, \false, null, \false, $isCollectors);
    }
    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function createFromInput(InputInterface $input) : Configuration
    {
        $isDryRun = (bool) $input->getOption(\Rector\Core\Configuration\Option::DRY_RUN);
        $shouldClearCache = (bool) $input->getOption(\Rector\Core\Configuration\Option::CLEAR_CACHE);
        $outputFormat = (string) $input->getOption(\Rector\Core\Configuration\Option::OUTPUT_FORMAT);
        $showProgressBar = $this->shouldShowProgressBar($input, $outputFormat);
        $showDiffs = $this->shouldShowDiffs($input);
        $paths = $this->resolvePaths($input);
        $fileExtensions = SimpleParameterProvider::provideArrayParameter(\Rector\Core\Configuration\Option::FILE_EXTENSIONS);
        $isParallel = SimpleParameterProvider::provideBoolParameter(\Rector\Core\Configuration\Option::PARALLEL);
        $parallelPort = (string) $input->getOption(\Rector\Core\Configuration\Option::PARALLEL_PORT);
        $parallelIdentifier = (string) $input->getOption(\Rector\Core\Configuration\Option::PARALLEL_IDENTIFIER);
        $isDebug = (bool) $input->getOption(\Rector\Core\Configuration\Option::DEBUG);
        $memoryLimit = $this->resolveMemoryLimit($input);
        $isCollectors = SimpleParameterProvider::provideBoolParameter(\Rector\Core\Configuration\Option::COLLECTORS);
        return new Configuration($isDryRun, $showProgressBar, $shouldClearCache, $outputFormat, $fileExtensions, $paths, $showDiffs, $parallelPort, $parallelIdentifier, $isParallel, $memoryLimit, $isDebug, $isCollectors);
    }
    private function shouldShowProgressBar(InputInterface $input, string $outputFormat) : bool
    {
        $noProgressBar = (bool) $input->getOption(\Rector\Core\Configuration\Option::NO_PROGRESS_BAR);
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
        $noDiffs = (bool) $input->getOption(\Rector\Core\Configuration\Option::NO_DIFFS);
        if ($noDiffs) {
            return \false;
        }
        // fallback to parameter
        return !SimpleParameterProvider::provideBoolParameter(\Rector\Core\Configuration\Option::NO_DIFFS, \false);
    }
    /**
     * @return string[]|mixed[]
     */
    private function resolvePaths(InputInterface $input) : array
    {
        $commandLinePaths = (array) $input->getArgument(\Rector\Core\Configuration\Option::SOURCE);
        // give priority to command line
        if ($commandLinePaths !== []) {
            return $commandLinePaths;
        }
        // fallback to parameter
        return SimpleParameterProvider::provideArrayParameter(\Rector\Core\Configuration\Option::PATHS);
    }
    private function resolveMemoryLimit(InputInterface $input) : ?string
    {
        $memoryLimit = $input->getOption(\Rector\Core\Configuration\Option::MEMORY_LIMIT);
        if ($memoryLimit !== null) {
            return (string) $memoryLimit;
        }
        if (!SimpleParameterProvider::hasParameter(\Rector\Core\Configuration\Option::MEMORY_LIMIT)) {
            return null;
        }
        return SimpleParameterProvider::provideStringParameter(\Rector\Core\Configuration\Option::MEMORY_LIMIT);
    }
}
