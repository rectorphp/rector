<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use RectorPrefix202212\Nette\Utils\Json;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Bootstrap\RectorConfigsResolver;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\ConsoleApplication;
use Rector\Core\Console\Style\RectorConsoleOutputStyleFactory;
use Rector\Core\DependencyInjection\RectorContainerFactory;
use Rector\Core\Kernel\RectorKernel;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use RectorPrefix202212\Symfony\Component\Console\Command\Command;
use RectorPrefix202212\Symfony\Component\Console\Input\ArgvInput;
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
        if (\class_exists(RectorKernel::class)) {
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
        $realPath = \realpath($filePath);
        if (!\is_string($realPath)) {
            return;
        }
        $this->alreadyLoadedAutoloadFiles[] = $realPath;
        require_once $filePath;
    }
}
if (\file_exists(__DIR__ . '/../preload.php') && \is_dir(__DIR__ . '/../vendor')) {
    require_once __DIR__ . '/../preload.php';
}
require_once __DIR__ . '/../src/constants.php';
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
        $rectorConsoleOutputStyleFactory = new RectorConsoleOutputStyleFactory(new PrivatesAccessor());
        $rectorConsoleOutputStyle = $rectorConsoleOutputStyleFactory->create();
        $rectorConsoleOutputStyle->error($throwable->getMessage());
    }
    exit(Command::FAILURE);
}
/** @var ConsoleApplication $application */
$application = $container->get(ConsoleApplication::class);
exit($application->run());
