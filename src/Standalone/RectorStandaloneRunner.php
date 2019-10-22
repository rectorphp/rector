<?php

declare(strict_types=1);

namespace Rector\Standalone;

use Psr\Container\ContainerInterface;
use Rector\Application\ErrorAndDiffCollector;
use Rector\Application\RectorApplication;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\Configuration\Configuration;
use Rector\Configuration\Option;
use Rector\Console\Command\ProcessCommand;
use Rector\Console\Output\ConsoleOutputFormatter;
use Rector\DependencyInjection\RectorContainerFactory;
use Rector\Exception\FileSystem\FileNotFoundException;
use Rector\Extension\FinishingExtensionRunner;
use Rector\Extension\ReportingExtensionRunner;
use Rector\FileSystem\FilesFinder;
use Rector\Guard\RectorGuard;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Stubs\StubLoader;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    public function __construct(RectorContainerFactory $rectorContainerFactory, SymfonyStyle $symfonyStyle)
    {
        $this->rectorContainerFactory = $rectorContainerFactory;
        $this->nativeSymfonyStyle = $symfonyStyle;
    }

    /**
     * @param string[] $source
     */
    public function processSourceWithSet(array $source, string $set, bool $isDryRun): void
    {
        $source = $this->absolutizeSource($source);

        $container = $this->rectorContainerFactory->createFromSet($set);
        $this->prepare($container, $source, $isDryRun);

        /** @var FilesFinder $filesFinder */
        $filesFinder = $container->get(FilesFinder::class);
        $phpFileInfos = $filesFinder->findInDirectoriesAndFiles($source, ['php']);

        /** @var RectorApplication $rectorApplication */
        $rectorApplication = $container->get(RectorApplication::class);
        $rectorApplication->runOnFileInfos($phpFileInfos);

        $this->reportErrors($container);

        $this->finish($container);
    }

    /**
     * Mostly copied from: https://github.com/rectorphp/rector/blob/master/src/Console/Command/ProcessCommand.php.
     * @param string[] $source
     */
    private function prepare(ContainerInterface $container, array $source, bool $isDryRun): void
    {
        ini_set('memory_limit', '4096M');

        /** @var RectorNodeTraverser $rectorNodeTraverser */
        $rectorNodeTraverser = $container->get(RectorNodeTraverser::class);
        $this->prepareConfiguration($container, $rectorNodeTraverser, $isDryRun);

        /** @var RectorGuard $rectorGuard */
        $rectorGuard = $container->get(RectorGuard::class);
        $rectorGuard->ensureSomeRectorsAreRegistered();

        // setup verbosity from the current run
        /** @var SymfonyStyle $symfonyStyle */
        $symfonyStyle = $container->get(SymfonyStyle::class);
        $symfonyStyle->setVerbosity($this->nativeSymfonyStyle->getVerbosity());

        /** @var AdditionalAutoloader $additionalAutoloader */
        $additionalAutoloader = $container->get(AdditionalAutoloader::class);
        $additionalAutoloader->autoloadWithInputAndSource(new ArrayInput([]), $source);

        /** @var StubLoader $stubLoader */
        $stubLoader = $container->get(StubLoader::class);
        $stubLoader->loadStubs();
    }

    private function reportErrors(ContainerInterface $container): void
    {
        /** @var ErrorAndDiffCollector $errorAndDiffCollector */
        $errorAndDiffCollector = $container->get(ErrorAndDiffCollector::class);

        /** @var ConsoleOutputFormatter $consoleOutputFormatter */
        $consoleOutputFormatter = $container->get(ConsoleOutputFormatter::class);
        $consoleOutputFormatter->report($errorAndDiffCollector);
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

    private function finish(ContainerInterface $container): void
    {
        /** @var FinishingExtensionRunner $finishingExtensionRunner */
        $finishingExtensionRunner = $container->get(FinishingExtensionRunner::class);
        $finishingExtensionRunner->run();

        /** @var ReportingExtensionRunner $reportingExtensionRunner */
        $reportingExtensionRunner = $container->get(ReportingExtensionRunner::class);
        $reportingExtensionRunner->run();
    }

    private function prepareConfiguration(
        ContainerInterface $container,
        RectorNodeTraverser $rectorNodeTraverser,
        bool $isDryRun
    ): void {
        /** @var Configuration $configuration */
        $configuration = $container->get(Configuration::class);

        $configuration->setAreAnyPhpRectorsLoaded((bool) $rectorNodeTraverser->getPhpRectorCount());

        // definition mimics @see ProcessCommand definition
        /** @var ProcessCommand $processCommand */
        $processCommand = $container->get(ProcessCommand::class);
        $definition = clone $processCommand->getDefinition();

        // reset arguments to prevent "source is missing"
        $definition->setArguments([]);

        $configuration->resolveFromInput(new ArrayInput([
            '--' . Option::OPTION_DRY_RUN => $isDryRun,
            '--' . Option::OPTION_OUTPUT_FORMAT => 'console',
        ], $definition));
    }
}
