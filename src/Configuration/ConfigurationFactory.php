<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\ValueObject\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ConfigurationFactory
{
    public function __construct(
        private ParameterProvider $parameterProvider
    ) {
    }

    public function createForTests(): Configuration
    {
        $fileExtensions = $this->parameterProvider->provideArrayParameter(Option::FILE_EXTENSIONS);

        return new Configuration(isDryRun: false, fileExtensions: $fileExtensions);
    }

    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function createFromInput(InputInterface $input): Configuration
    {
        $isDryRun = (bool) $input->getOption(Option::DRY_RUN);
        $shouldClearCache = (bool) $input->getOption(Option::CLEAR_CACHE);

        $outputFormat = (string) $input->getOption(Option::OUTPUT_FORMAT);
        $showProgressBar = $this->shouldShowProgressBar($input, $outputFormat);

        $showDiffs = ! (bool) $input->getOption(Option::NO_DIFFS);

        $paths = $this->resolvePaths($input);

        $fileExtensions = $this->parameterProvider->provideArrayParameter(Option::FILE_EXTENSIONS);

        return new Configuration(
            $isDryRun,
            $showProgressBar,
            $shouldClearCache,
            $outputFormat,
            $fileExtensions,
            $paths,
            $showDiffs
        );
    }

    private function shouldShowProgressBar(InputInterface $input, string $outputFormat): bool
    {
        $noProgressBar = (bool) $input->getOption(Option::NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return false;
        }

        return $outputFormat === ConsoleOutputFormatter::NAME;
    }

    /**
     * @param string[] $commandLinePaths
     * @return string[]
     */
    private function correctBashSpacePaths(array $commandLinePaths): array
    {
        // fixes bash edge-case that to merges string with space to one
        foreach ($commandLinePaths as $commandLinePath) {
            if (\str_contains($commandLinePath, ' ')) {
                $commandLinePaths = explode(' ', $commandLinePath);
            }
        }

        return $commandLinePaths;
    }

    /**
     * @return string[]|mixed[]
     */
    private function resolvePaths(InputInterface $input): array
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
