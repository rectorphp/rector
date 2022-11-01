<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\ValueObject\Configuration;
use RectorPrefix202211\Symfony\Component\Console\Input\InputInterface;
final class ConfigurationFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Core\Contract\Console\OutputStyleInterface
     */
    private $rectorOutputStyle;
    public function __construct(ParameterProvider $parameterProvider, OutputStyleInterface $rectorOutputStyle)
    {
        $this->parameterProvider = $parameterProvider;
        $this->rectorOutputStyle = $rectorOutputStyle;
    }
    /**
     * @param string[] $paths
     */
    public function createForTests(array $paths) : Configuration
    {
        $fileExtensions = $this->parameterProvider->provideArrayParameter(\Rector\Core\Configuration\Option::FILE_EXTENSIONS);
        return new Configuration(\true, \true, \false, ConsoleOutputFormatter::NAME, $fileExtensions, $paths);
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
        $showDiffs = !(bool) $input->getOption(\Rector\Core\Configuration\Option::NO_DIFFS);
        $paths = $this->resolvePaths($input);
        $fileExtensions = $this->parameterProvider->provideArrayParameter(\Rector\Core\Configuration\Option::FILE_EXTENSIONS);
        $isParallel = $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::PARALLEL);
        $parallelPort = (string) $input->getOption(\Rector\Core\Configuration\Option::PARALLEL_PORT);
        $parallelIdentifier = (string) $input->getOption(\Rector\Core\Configuration\Option::PARALLEL_IDENTIFIER);
        /** @var string|null $memoryLimit */
        $memoryLimit = $input->getOption(\Rector\Core\Configuration\Option::MEMORY_LIMIT);
        return new Configuration($isDryRun, $showProgressBar, $shouldClearCache, $outputFormat, $fileExtensions, $paths, $showDiffs, $parallelPort, $parallelIdentifier, $isParallel, $memoryLimit);
    }
    private function shouldShowProgressBar(InputInterface $input, string $outputFormat) : bool
    {
        $noProgressBar = (bool) $input->getOption(\Rector\Core\Configuration\Option::NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return \false;
        }
        if ($this->rectorOutputStyle->isVerbose()) {
            return \false;
        }
        return $outputFormat === ConsoleOutputFormatter::NAME;
    }
    /**
     * @param string[] $commandLinePaths
     * @return string[]
     */
    private function correctBashSpacePaths(array $commandLinePaths) : array
    {
        // fixes bash edge-case that to merges string with space to one
        foreach ($commandLinePaths as $commandLinePath) {
            if (\strpos($commandLinePath, ' ') !== \false) {
                $commandLinePaths = \explode(' ', $commandLinePath);
            }
        }
        return $commandLinePaths;
    }
    /**
     * @return string[]|mixed[]
     */
    private function resolvePaths(InputInterface $input) : array
    {
        $commandLinePaths = (array) $input->getArgument(\Rector\Core\Configuration\Option::SOURCE);
        // command line has priority
        if ($commandLinePaths !== []) {
            return $this->correctBashSpacePaths($commandLinePaths);
        }
        // fallback to parameter
        return $this->parameterProvider->provideArrayParameter(\Rector\Core\Configuration\Option::PATHS);
    }
}
