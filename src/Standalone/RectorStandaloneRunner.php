<?php

declare(strict_types=1);

namespace Rector\Core\Standalone;

use Psr\Container\ContainerInterface;
use Rector\Core\Application\ErrorAndDiffCollector;
use Rector\Core\Application\RectorApplication;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Command\ProcessCommand;
use Rector\Core\Console\Output\ConsoleOutputFormatter;
use Rector\Core\DependencyInjection\RectorContainerFactory;
use Rector\Core\Exception\FileSystem\FileNotFoundException;
use Rector\Core\Extension\FinishingExtensionRunner;
use Rector\Core\Extension\ReportingExtensionRunner;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\Guard\RectorGuard;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\Stubs\StubLoader;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * This class is needed over process/cli run to get console output in sane way;
 * without it, it's not possible to get inside output closed stream.
 */
final class RectorStandaloneRunner
{
    /**
     * @var RectorContainerFactory
     */
    private $rectorContainerFactory;

    /**
     * @var SymfonyStyle
     */
    private $nativeSymfonyStyle;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(RectorContainerFactory $rectorContainerFactory, SymfonyStyle $symfonyStyle)
    {
        $this->rectorContainerFactory = $rectorContainerFactory;
        $this->nativeSymfonyStyle = $symfonyStyle;
    }

    /**
     * @param string[] $source
     */
    public function processSourceWithSet(
        array $source,
        string $set,
        bool $isDryRun,
        bool $isQuietMode = false
    ): ErrorAndDiffCollector {
        $source = $this->absolutizeSource($source);

        $this->container = $this->rectorContainerFactory->createFromSet($set);

        // silent Symfony style
        if ($isQuietMode) {
            $this->nativeSymfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        $this->prepare($source, $isDryRun);

        $phpFileInfos = $this->findFilesInSource($source);
        $this->runRectorOnFileInfos($phpFileInfos);

        if (! $isQuietMode) {
            $this->reportErrors();
        }

        $this->finish();

        return $this->container->get(ErrorAndDiffCollector::class);
    }

    /**
     * @param string[] $source
     * @return string[]
     */
    private function absolutizeSource(array $source): array
    {
        foreach ($source as $key => $singleSource) {
            /** @var string $singleSource */
            if (! file_exists($singleSource)) {
                throw new FileNotFoundException($singleSource);
            }

            /** @var string $realpath */
            $realpath = realpath($singleSource);
            $source[$key] = $realpath;
        }

        return $source;
    }

    /**
     * Mostly copied from: https://github.com/rectorphp/rector/blob/master/src/Console/Command/ProcessCommand.php.
     * @param string[] $source
     */
    private function prepare(array $source, bool $isDryRun): void
    {
        ini_set('memory_limit', '4096M');

        /** @var RectorNodeTraverser $rectorNodeTraverser */
        $rectorNodeTraverser = $this->container->get(RectorNodeTraverser::class);
        $this->prepareConfiguration($rectorNodeTraverser, $isDryRun);

        /** @var RectorGuard $rectorGuard */
        $rectorGuard = $this->container->get(RectorGuard::class);
        $rectorGuard->ensureSomeRectorsAreRegistered();

        // setup verbosity from the current run
        /** @var SymfonyStyle $symfonyStyle */
        $symfonyStyle = $this->container->get(SymfonyStyle::class);
        $symfonyStyle->setVerbosity($this->nativeSymfonyStyle->getVerbosity());

        /** @var AdditionalAutoloader $additionalAutoloader */
        $additionalAutoloader = $this->container->get(AdditionalAutoloader::class);
        $additionalAutoloader->autoloadWithInputAndSource(new ArrayInput([]), $source);

        /** @var StubLoader $stubLoader */
        $stubLoader = $this->container->get(StubLoader::class);
        $stubLoader->loadStubs();
    }

    /**
     * @param string[] $source
     * @return SmartFileInfo[]
     */
    private function findFilesInSource(array $source): array
    {
        /** @var FilesFinder $filesFinder */
        $filesFinder = $this->container->get(FilesFinder::class);

        return $filesFinder->findInDirectoriesAndFiles($source, ['php']);
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     */
    private function runRectorOnFileInfos(array $phpFileInfos): void
    {
        /** @var RectorApplication $rectorApplication */
        $rectorApplication = $this->container->get(RectorApplication::class);
        $rectorApplication->runOnFileInfos($phpFileInfos);
    }

    private function reportErrors(): void
    {
        /** @var ErrorAndDiffCollector $errorAndDiffCollector */
        $errorAndDiffCollector = $this->container->get(ErrorAndDiffCollector::class);

        /** @var ConsoleOutputFormatter $consoleOutputFormatter */
        $consoleOutputFormatter = $this->container->get(ConsoleOutputFormatter::class);
        $consoleOutputFormatter->report($errorAndDiffCollector);
    }

    private function finish(): void
    {
        /** @var FinishingExtensionRunner $finishingExtensionRunner */
        $finishingExtensionRunner = $this->container->get(FinishingExtensionRunner::class);
        $finishingExtensionRunner->run();

        /** @var ReportingExtensionRunner $reportingExtensionRunner */
        $reportingExtensionRunner = $this->container->get(ReportingExtensionRunner::class);
        $reportingExtensionRunner->run();
    }

    private function prepareConfiguration(RectorNodeTraverser $rectorNodeTraverser, bool $isDryRun): void
    {
        /** @var Configuration $configuration */
        $configuration = $this->container->get(Configuration::class);

        $configuration->setAreAnyPhpRectorsLoaded((bool) $rectorNodeTraverser->getPhpRectorCount());

        // definition mimics @see ProcessCommand definition
        /** @var ProcessCommand $processCommand */
        $processCommand = $this->container->get(ProcessCommand::class);
        $definition = clone $processCommand->getDefinition();

        // reset arguments to prevent "source is missing"
        $definition->setArguments([]);

        $configuration->resolveFromInput(new ArrayInput([
            '--' . Option::OPTION_DRY_RUN => $isDryRun,
            '--' . Option::OPTION_OUTPUT_FORMAT => 'console',
        ], $definition));
    }
}
