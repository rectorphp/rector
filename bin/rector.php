<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use RectorPrefix202407\Nette\Utils\Json;
use Rector\Bootstrap\RectorConfigsResolver;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Configuration\Option;
use Rector\Console\Style\SymfonyStyleFactory;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\DependencyInjection\RectorContainerFactory;
use Rector\Util\Reflection\PrivatesAccessor;
use RectorPrefix202407\Symfony\Component\Console\Application;
use RectorPrefix202407\Symfony\Component\Console\Command\Command;
use RectorPrefix202407\Symfony\Component\Console\Input\ArgvInput;
// @ intentionally: continue anyway
@\ini_set('memory_limit', '-1');
// Performance boost
\error_reporting(\E_ALL);
\ini_set('display_errors', 'stderr');
\gc_disable();
\define('__RECTOR_RUNNING__', \true);
// Require Composer autoload.php
$autoloadIncluder = new AutoloadIncluder();
$autoloadIncluder->includeDependencyOrRepositoryVendorAutoloadIfExists();
final class AutoloadIncluder
{
    /**
     * @var string[]
     */
    private $alreadyLoadedAutoloadFiles = [];
    public function includeDependencyOrRepositoryVendorAutoloadIfExists() : void
    {
        // Rector's vendor is already loaded
        if (\class_exists(LazyContainerFactory::class)) {
            return;
        }
        // in Rector develop repository
        $this->loadIfExistsAndNotLoadedYet(__DIR__ . '/../vendor/autoload.php');
    }
    /**
     * In case Rector is installed as vendor dependency,
     * this autoloads the project vendor/autoload.php, including Rector
     */
    public function autoloadProjectAutoloaderFile() : void
    {
        $this->loadIfExistsAndNotLoadedYet(__DIR__ . '/../../../autoload.php');
    }
    /**
     * In case Rector is installed as global dependency
     */
    public function autoloadRectorInstalledAsGlobalDependency() : void
    {
        if (\dirname(__DIR__) === \dirname(\getcwd(), 2)) {
            return;
        }
        if (\is_dir('vendor/rector/rector')) {
            return;
        }
        $this->loadIfExistsAndNotLoadedYet('vendor/autoload.php');
    }
    public function autoloadFromCommandLine() : void
    {
        $cliArgs = $_SERVER['argv'];
        $aOptionPosition = \array_search('-a', $cliArgs, \true);
        $autoloadFileOptionPosition = \array_search('--autoload-file', $cliArgs, \true);
        if (\is_int($aOptionPosition)) {
            $autoloadOptionPosition = $aOptionPosition;
        } elseif (\is_int($autoloadFileOptionPosition)) {
            $autoloadOptionPosition = $autoloadFileOptionPosition;
        } else {
            return;
        }
        $autoloadFileValuePosition = $autoloadOptionPosition + 1;
        $fileToAutoload = $cliArgs[$autoloadFileValuePosition] ?? null;
        if ($fileToAutoload === null) {
            return;
        }
        $this->loadIfExistsAndNotLoadedYet($fileToAutoload);
    }
    public function loadIfExistsAndNotLoadedYet(string $filePath) : void
    {
        if (!\file_exists($filePath)) {
            return;
        }
        if (\in_array($filePath, $this->alreadyLoadedAutoloadFiles, \true)) {
            return;
        }
        /** @var string $realPath always string after file_exists() check */
        $realPath = \realpath($filePath);
        $this->alreadyLoadedAutoloadFiles[] = $realPath;
        require_once $filePath;
    }
}
\class_alias('RectorPrefix202407\\AutoloadIncluder', 'AutoloadIncluder', \false);
if (\file_exists(__DIR__ . '/../preload.php') && \is_dir(__DIR__ . '/../vendor')) {
    require_once __DIR__ . '/../preload.php';
}
// require rector-src on split packages
if (\file_exists(__DIR__ . '/../preload-split-package.php') && \is_dir(__DIR__ . '/../../../../vendor')) {
    require_once __DIR__ . '/../preload-split-package.php';
}
$autoloadIncluder->loadIfExistsAndNotLoadedYet(__DIR__ . '/../vendor/scoper-autoload.php');
$autoloadIncluder->autoloadProjectAutoloaderFile();
$autoloadIncluder->autoloadRectorInstalledAsGlobalDependency();
$autoloadIncluder->autoloadFromCommandLine();
$rectorConfigsResolver = new RectorConfigsResolver();
try {
    $bootstrapConfigs = $rectorConfigsResolver->provide();
    $rectorContainerFactory = new RectorContainerFactory();
    $container = $rectorContainerFactory->createFromBootstrapConfigs($bootstrapConfigs);
} catch (\Throwable $throwable) {
    // for json output
    $argvInput = new ArgvInput();
    $outputFormat = $argvInput->getParameterOption('--' . Option::OUTPUT_FORMAT);
    // report fatal error in json format
    if ($outputFormat === JsonOutputFormatter::NAME) {
        echo Json::encode(['fatal_errors' => [$throwable->getMessage()]]);
    } else {
        // report fatal errors in console format
        $symfonyStyleFactory = new SymfonyStyleFactory(new PrivatesAccessor());
        $symfonyStyle = $symfonyStyleFactory->create();
        $symfonyStyle->error($throwable->getMessage());
    }
    exit(Command::FAILURE);
}
/** @var Application $application */
$application = $container->get(Application::class);
exit($application->run());
