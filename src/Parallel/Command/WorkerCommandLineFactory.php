<?php

declare (strict_types=1);
namespace Rector\Parallel\Command;

use PHPStan\Turbo\TurboExtensionSelector;
use Rector\Bootstrap\CliOptionValueResolver;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Configuration\Option;
use Rector\FileSystem\FilePathHelper;
use Rector\ValueObject\Configuration;
/**
 * @see \Rector\Tests\Parallel\Command\WorkerCommandLineFactoryTest
 */
final class WorkerCommandLineFactory
{
    /**
     * @readonly
     */
    private FilePathHelper $filePathHelper;
    /**
     * @var string
     */
    private const OPTION_DASHES = '--';
    public function __construct(FilePathHelper $filePathHelper)
    {
        $this->filePathHelper = $filePathHelper;
    }
    /**
     * Build the worker sub-process command line from the resolved Configuration
     */
    public function create(string $mainScript, string $workerCommandName, Configuration $configuration, string $identifier, int $port): string
    {
        $workerCommandArray = array_merge([escapeshellarg(\PHP_BINARY)], array_map(\Closure::fromCallable('escapeshellarg'), $this->createTurboExtensionArguments()), [escapeshellarg($mainScript), $workerCommandName]);
        $workerCommandArray = array_merge($workerCommandArray, $this->createFlagOptions($configuration), $this->createValueOptions($configuration));
        // for TCP local server
        $workerCommandArray[] = self::OPTION_DASHES . Option::PARALLEL_PORT;
        $workerCommandArray[] = (string) $port;
        $workerCommandArray[] = self::OPTION_DASHES . Option::PARALLEL_IDENTIFIER;
        $workerCommandArray[] = escapeshellarg($identifier);
        foreach ($configuration->getPaths() as $path) {
            $workerCommandArray[] = escapeshellarg($path);
        }
        // set json output
        $workerCommandArray[] = self::OPTION_DASHES . Option::OUTPUT_FORMAT;
        $workerCommandArray[] = escapeshellarg(JsonOutputFormatter::NAME);
        // disable colors, breaks json_decode() otherwise
        // @see https://github.com/symfony/symfony/issues/1238
        $workerCommandArray[] = '--no-ansi';
        // only pass --config if explicitly set via command line, otherwise the worker
        // resolves it through the RectorConfigsResolver fallback, same as the main process
        $config = CliOptionValueResolver::resolve($_SERVER['argv'] ?? [], ['--config', '-c']);
        if ($config !== null && $config !== '') {
            $workerCommandArray[] = self::OPTION_DASHES . Option::CONFIG;
            // escaped, so a path with space stays one argument
            $workerCommandArray[] = escapeshellarg($this->filePathHelper->relativePath($config));
        }
        return implode(' ', $workerCommandArray);
    }
    /**
     * @return string[]
     */
    private function createFlagOptions(Configuration $configuration): array
    {
        $flagOptions = [
            Option::DRY_RUN => $configuration->isDryRun(),
            Option::CLEAR_CACHE => $configuration->shouldClearCache(),
            Option::NO_DIFFS => !$configuration->shouldShowDiffs(),
            Option::COMPOSER_BASED => $configuration->isComposerBased(),
            Option::PHP => $configuration->isPhpOnly(),
            Option::DEBUG => $configuration->isDebug(),
            // read from argv, as it is handled in bin/rector.php before the configuration exists
            Option::XDEBUG => in_array(self::OPTION_DASHES . Option::XDEBUG, $_SERVER['argv'] ?? [], \true),
        ];
        $workerOptions = [];
        foreach ($flagOptions as $optionName => $isEnabled) {
            if ($isEnabled) {
                $workerOptions[] = self::OPTION_DASHES . $optionName;
            }
        }
        return $workerOptions;
    }
    /**
     * @return string[]
     */
    private function createValueOptions(Configuration $configuration): array
    {
        $workerOptions = [];
        // read from argv, as it is autoloaded in bin/rector.php before the configuration exists;
        // the worker needs the space-separated form there
        $autoloadFile = CliOptionValueResolver::resolve($_SERVER['argv'] ?? [], ['--autoload-file', '-a']);
        if ($autoloadFile !== null && $autoloadFile !== '') {
            $workerOptions[] = self::OPTION_DASHES . Option::AUTOLOAD_FILE;
            $workerOptions[] = escapeshellarg($autoloadFile);
        }
        // repeated --only, one flag per value
        foreach ($configuration->getOnlyRules() as $onlyRule) {
            $workerOptions[] = self::OPTION_DASHES . Option::ONLY;
            $workerOptions[] = escapeshellarg($onlyRule);
        }
        $onlySuffix = $configuration->getOnlySuffix();
        if ($onlySuffix !== null && $onlySuffix !== '') {
            $workerOptions[] = self::OPTION_DASHES . Option::ONLY_SUFFIX;
            $workerOptions[] = escapeshellarg($onlySuffix);
        }
        // the worker scopes the cache by filters too
        if ($configuration->getFilters() !== []) {
            $workerOptions[] = self::OPTION_DASHES . Option::FILTER;
            $workerOptions[] = escapeshellarg(implode(',', $configuration->getFilters()));
        }
        $memoryLimit = $configuration->getMemoryLimit();
        if ($memoryLimit !== null && $memoryLimit !== '') {
            // "=" form, symfony/console does not accept -1 as value without assign
            $workerOptions[] = self::OPTION_DASHES . Option::MEMORY_LIMIT . '=' . escapeshellarg($memoryLimit);
        }
        $maxChanges = $configuration->getMaxChanges();
        if ($maxChanges !== null) {
            $workerOptions[] = self::OPTION_DASHES . Option::MAX_CHANGES;
            $workerOptions[] = escapeshellarg((string) $maxChanges);
        }
        return $workerOptions;
    }
    /**
     * The workers get PHPStan Turbo on their command line, so the main process needs no restart to enable it.
     *
     * @return string[]
     */
    private function createTurboExtensionArguments(): array
    {
        $extensionPath = TurboExtensionSelector::findExtensionForWorkers();
        if ($extensionPath === null) {
            return [];
        }
        // quote the value, so PHP parses it as a string even when the path contains an operator like ~
        return ['-d', "extension='" . $extensionPath . "'", '-d', TurboExtensionSelector::EXTENSION_PATH_INI . "='" . $extensionPath . "'"];
    }
}
