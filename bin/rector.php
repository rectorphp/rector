<?php

declare(strict_types=1);

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Bootstrap\ConfigShifter;
use Rector\Core\Bootstrap\RectorConfigsResolver;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Console\ConsoleApplication;
use Rector\Core\Console\Style\SymfonyStyleFactory;
use Rector\Core\DependencyInjection\RectorContainerFactory;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;
use Symplify\SetConfigResolver\Bootstrap\InvalidSetReporter;
use Symplify\SetConfigResolver\Exception\SetNotFoundException;

// @ intentionally: continue anyway
@ini_set('memory_limit', '-1');

// Performance boost
error_reporting(E_ALL);
ini_set('display_errors', 'stderr');
gc_disable();

define('__RECTOR_RUNNING__', true);

// Require Composer autoload.php
$autoloadIncluder = new AutoloadIncluder();
$autoloadIncluder->includeCwdVendorAutoloadIfExists();
$autoloadIncluder->autoloadProjectAutoloaderFile('/../../autoload.php');
$autoloadIncluder->includeDependencyOrRepositoryVendorAutoloadIfExists();
$autoloadIncluder->autoloadFromCommandLine();

$symfonyStyleFactory = new SymfonyStyleFactory(new PrivatesCaller());
$symfonyStyle = $symfonyStyleFactory->create();

try {
    $rectorConfigsResolver = new RectorConfigsResolver();
    $configFileInfos = $rectorConfigsResolver->provide();

    // Build DI container
    $rectorContainerFactory = new RectorContainerFactory();

    // shift configs as last so parameters with main config have higher priority
    $configShifter = new ConfigShifter();
    $firstResolvedConfig = $rectorConfigsResolver->getFirstResolvedConfig();
    if ($firstResolvedConfig !== null) {
        $configFileInfos = $configShifter->shiftInputConfigAsLast($configFileInfos, $firstResolvedConfig);
    }

    $container = $rectorContainerFactory->createFromConfigs($configFileInfos);

    $firstResolvedConfig = $rectorConfigsResolver->getFirstResolvedConfig();
    if ($firstResolvedConfig) {
        /** @var Configuration $configuration */
        $configuration = $container->get(Configuration::class);
        $configuration->setFirstResolverConfigFileInfo($firstResolvedConfig);

        /** @var ChangedFilesDetector $changedFilesDetector */
        $changedFilesDetector = $container->get(ChangedFilesDetector::class);
        $changedFilesDetector->setFirstResolvedConfigFileInfo($firstResolvedConfig);
    }
} catch (SetNotFoundException $setNotFoundException) {
    $invalidSetReporter = new InvalidSetReporter();
    $invalidSetReporter->report($setNotFoundException);
    exit(ShellCode::ERROR);
} catch (Throwable $throwable) {
    $symfonyStyle->error($throwable->getMessage());
    exit(ShellCode::ERROR);
}

/** @var ConsoleApplication $application */
$application = $container->get(ConsoleApplication::class);
exit($application->run());

final class AutoloadIncluder
{
    /**
     * @var string[]
     */
    private $alreadyLoadedAutoloadFiles = [];

    public function includeCwdVendorAutoloadIfExists(): void
    {
        $cwdVendorAutoload = getcwd() . '/vendor/autoload.php';
        if (! is_file($cwdVendorAutoload)) {
            return;
        }

        $this->loadIfNotLoadedYet($cwdVendorAutoload, __METHOD__ . '()" on line ' . __LINE__);
    }

    public function includeDependencyOrRepositoryVendorAutoloadIfExists(): void
    {
        // Rector's vendor is already loaded
        if (class_exists(RectorKernel::class)) {
            return;
        }

        $devOrPharVendorAutoload = __DIR__ . '/../vendor/autoload.php';
        if (! is_file($devOrPharVendorAutoload)) {
            return;
        }

        $this->loadIfNotLoadedYet($devOrPharVendorAutoload, __METHOD__ . '()" on line ' . __LINE__);
    }

    /**
     * Inspired by https://github.com/phpstan/phpstan-src/blob/e2308ecaf49a9960510c47f5a992ce7b27f6dba2/bin/phpstan#L19
     */
    public function autoloadProjectAutoloaderFile(string $file): void
    {
        $path = dirname(__DIR__) . $file;
        if (! extension_loaded('phar')) {
            if (is_file($path)) {
                $this->loadIfNotLoadedYet($path, __METHOD__ . '()" on line ' . __LINE__);
            }
            return;
        }

        $pharPath = Phar::running(false);
        if ($pharPath === '') {
            if (is_file($path)) {
                $this->loadIfNotLoadedYet($path, __METHOD__ . '()" on line ' . __LINE__);
            }
        } else {
            $path = dirname($pharPath) . $file;
            if (is_file($path)) {
                $this->loadIfNotLoadedYet($path, __METHOD__ . '()" on line ' . __LINE__);
            }
        }
    }

    public function autoloadFromCommandLine(): void
    {
        $cliArgs = $_SERVER['argv'];

        $autoloadOptionPosition = array_search('-a', $cliArgs, true) ?: array_search('--autoload-file', $cliArgs, true);
        if (! $autoloadOptionPosition) {
            return;
        }

        $autoloadFileValuePosition = $autoloadOptionPosition + 1;
        $fileToAutoload = $cliArgs[$autoloadFileValuePosition] ?? null;
        if ($fileToAutoload === null) {
            return;
        }

        $this->loadIfNotLoadedYet($fileToAutoload, __METHOD__);
    }

    private function loadIfNotLoadedYet(string $file, string $location): void
    {
        if (in_array($file, $this->alreadyLoadedAutoloadFiles, true)) {
            return;
        }

        if ($this->isDebugOption()) {
            echo sprintf(sprintf('File "%s" is about to be loaded in "%s"' . PHP_EOL, $file, $location));
        }

        $this->alreadyLoadedAutoloadFiles[] = realpath($file);

        require_once $file;
    }

    private function isDebugOption(): bool
    {
        return in_array('--debug', $_SERVER['argv'], true);
    }
}
