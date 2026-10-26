<?php

declare (strict_types=1);
namespace RectorPrefix202610;

use RectorPrefix202610\Composer\XdebugHandler\XdebugHandler;
use RectorPrefix202610\Nette\Utils\Json;
use PHPStan\PharAutoloader;
use PHPStan\Turbo\TurboExtensionEnabler;
use Rector\Bootstrap\AutoloadFileParameterResolver;
use Rector\Bootstrap\RectorConfigsResolver;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Configuration\Option;
use Rector\Console\Style\SymfonyStyleFactory;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\DependencyInjection\RectorContainerFactory;
use Rector\Util\Reflection\PrivatesAccessor;
use RectorPrefix202610\Symfony\Component\Console\Application;
use RectorPrefix202610\Symfony\Component\Console\Command\Command;
use RectorPrefix202610\Symfony\Component\Console\Input\ArgvInput;
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
// PHPStan Turbo replaces php-parser classes, so it is activated before preload.php declares them.
// The main process only gets Turbo when php.ini loads it; the workers get it from WorkerCommandLineFactory.
$phpstanBootstrapFile = __DIR__ . '/../../../phpstan/phpstan/bootstrap.php';
if (!\class_exists(PharAutoloader::class) && \is_file($phpstanBootstrapFile)) {
    // Rector installed as a dependency: the project autoloader that loads PHPStan comes after preload.php,
    // so load PHPStan's bootstrap now, and mark it as loaded with the identifier Composer gives the file
    $GLOBALS['__composer_autoload_files'][\md5('phpstan/phpstan:bootstrap.php')] = \true;
    require_once $phpstanBootstrapFile;
}
if (\class_exists(PharAutoloader::class)) {
    $phpstanPharPath = \dirname((string) (new \ReflectionClass(PharAutoloader::class))->getFileName()) . '/phpstan.phar';
    TurboExtensionEnabler::trustOwnTypesIfSuitable($_SERVER['argv'], $phpstanPharPath);
    TurboExtensionEnabler::activateIfCompatible($phpstanPharPath);
    unset($phpstanPharPath);
}
unset($phpstanBootstrapFile);
final class AutoloadIncluder
{
    /**
     * @var string[]
     */
    private array $alreadyLoadedAutoloadFiles = [];
    public function includeDependencyOrRepositoryVendorAutoloadIfExists(): void
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
    public function autoloadProjectAutoloaderFile(): void
    {
        $this->loadIfExistsAndNotLoadedYet(__DIR__ . '/../../../autoload.php');
    }
    /**
     * In case Rector is installed as global dependency
     */
    public function autoloadRectorInstalledAsGlobalDependency(): void
    {
        if (\dirname(__DIR__) === \dirname(\getcwd(), 2)) {
            return;
        }
        if (\is_dir('vendor/rector/rector')) {
            return;
        }
        $this->loadIfExistsAndNotLoadedYet('vendor/autoload.php');
    }
    public function autoloadFromCommandLine(): void
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
    public function loadIfExistsAndNotLoadedYet(string $filePath): void
    {
        if (!\file_exists($filePath)) {
            return;
        }
        if (\in_array($filePath, $this->alreadyLoadedAutoloadFiles, \true)) {
            return;
        }
        /** @var non-empty-string $realPath always string after file_exists() check */
        $realPath = \realpath($filePath);
        $this->alreadyLoadedAutoloadFiles[] = $realPath;
        require_once $filePath;
    }
}
\class_alias('RectorPrefix202610\AutoloadIncluder', 'AutoloadIncluder', \false);
if (\file_exists(__DIR__ . '/../preload.php') && \is_dir(__DIR__ . '/../vendor')) {
    require_once __DIR__ . '/../preload.php';
}
$autoloadIncluder->loadIfExistsAndNotLoadedYet(__DIR__ . '/../vendor/scoper-autoload.php');
$autoloadIncluder->autoloadProjectAutoloaderFile();
$autoloadIncluder->autoloadRectorInstalledAsGlobalDependency();
$autoloadIncluder->autoloadFromCommandLine();
// restart without Xdebug before the container is built, so the restarted process does not repeat that work
if (!(new ArgvInput())->hasParameterOption('--xdebug')) {
    $xdebugHandler = new XdebugHandler('rector');
    $xdebugHandler->setPersistent();
    $xdebugHandler->check();
    unset($xdebugHandler);
}
AutoloadFileParameterResolver::resolveFromArgv($_SERVER['argv']);
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
        $errors = [];
        do {
            $errors[] = $throwable->getMessage();
        } while ($throwable = $throwable->getPrevious());
        echo Json::encode(['fatal_errors' => $errors]);
    } else {
        // report fatal errors in console format
        $symfonyStyleFactory = new SymfonyStyleFactory(new PrivatesAccessor());
        $symfonyStyle = $symfonyStyleFactory->create();
        do {
            $symfonyStyle->error(\str_replace("\r\n", "\n", $throwable->getMessage()));
        } while ($throwable = $throwable->getPrevious());
    }
    exit(Command::FAILURE);
}
/** @var Application $application */
$application = $container->get(Application::class);
exit($application->run());
