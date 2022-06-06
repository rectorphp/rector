<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Configuration;

use RectorPrefix20220606\Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use RectorPrefix20220606\Rector\Core\Contract\Console\OutputStyleInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\Configuration;
use RectorPrefix20220606\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220606\Symplify\PackageBuilder\Parameter\ParameterProvider;
final class ConfigurationFactory
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
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
        $fileExtensions = $this->parameterProvider->provideArrayParameter(Option::FILE_EXTENSIONS);
        return new Configuration(\true, \true, \false, ConsoleOutputFormatter::NAME, $fileExtensions, $paths);
    }
    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function createFromInput(InputInterface $input) : Configuration
    {
        $isDryRun = (bool) $input->getOption(Option::DRY_RUN);
        $shouldClearCache = (bool) $input->getOption(Option::CLEAR_CACHE);
        $outputFormat = (string) $input->getOption(Option::OUTPUT_FORMAT);
        $showProgressBar = $this->shouldShowProgressBar($input, $outputFormat);
        $showDiffs = !(bool) $input->getOption(Option::NO_DIFFS);
        $paths = $this->resolvePaths($input);
        $fileExtensions = $this->parameterProvider->provideArrayParameter(Option::FILE_EXTENSIONS);
        $isParallel = $this->parameterProvider->provideBoolParameter(Option::PARALLEL);
        $parallelPort = (string) $input->getOption(Option::PARALLEL_PORT);
        $parallelIdentifier = (string) $input->getOption(Option::PARALLEL_IDENTIFIER);
        /** @var string|null $memoryLimit */
        $memoryLimit = $input->getOption(Option::MEMORY_LIMIT);
        return new Configuration($isDryRun, $showProgressBar, $shouldClearCache, $outputFormat, $fileExtensions, $paths, $showDiffs, $parallelPort, $parallelIdentifier, $isParallel, $memoryLimit);
    }
    private function shouldShowProgressBar(InputInterface $input, string $outputFormat) : bool
    {
        $noProgressBar = (bool) $input->getOption(Option::NO_PROGRESS_BAR);
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
        $commandLinePaths = (array) $input->getArgument(Option::SOURCE);
        // command line has priority
        if ($commandLinePaths !== []) {
            return $this->correctBashSpacePaths($commandLinePaths);
        }
        // fallback to parameter
        return $this->parameterProvider->provideArrayParameter(Option::PATHS);
    }
}
