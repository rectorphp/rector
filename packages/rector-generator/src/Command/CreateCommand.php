<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Command;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\RectorGenerator\Composer\ComposerPackageAutoloadUpdater;
use Rector\RectorGenerator\Config\ConfigFilesystem;
use Rector\RectorGenerator\Configuration\ConfigurationFactory;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\Generator\FileGenerator;
use Rector\RectorGenerator\Guard\OverrideGuard;
use Rector\RectorGenerator\TemplateVariablesFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CreateCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var TemplateVariablesFactory
     */
    private $templateVariablesFactory;

    /**
     * @var ComposerPackageAutoloadUpdater
     */
    private $composerPackageAutoloadUpdater;

    /**
     * @var TemplateFinder
     */
    private $templateFinder;

    /**
     * @var ConfigFilesystem
     */
    private $configFilesystem;

    /**
     * @var OverrideGuard
     */
    private $overrideGuard;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var FileGenerator
     */
    private $fileGenerator;

    public function __construct(
        ComposerPackageAutoloadUpdater $composerPackageAutoloadUpdater,
        ConfigFilesystem $configFilesystem,
        ConfigurationFactory $configurationFactory,
        FileGenerator $fileGenerator,
        OverrideGuard $overrideGuard,
        ParameterProvider $parameterProvider,
        SymfonyStyle $symfonyStyle,
        TemplateFinder $templateFinder,
        TemplateVariablesFactory $templateVariablesFactory
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->configurationFactory = $configurationFactory;
        $this->templateVariablesFactory = $templateVariablesFactory;
        $this->composerPackageAutoloadUpdater = $composerPackageAutoloadUpdater;
        $this->templateFinder = $templateFinder;
        $this->configFilesystem = $configFilesystem;
        $this->overrideGuard = $overrideGuard;
        $this->parameterProvider = $parameterProvider;
        $this->fileGenerator = $fileGenerator;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setAliases(['c']);
        $this->setDescription('[DEV] Create a new Rector, in a proper location, with new tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rectorRecipe = $this->parameterProvider->provideParameter(Option::RECTOR_RECIPE);

        $configuration = $this->configurationFactory->createFromRectorRecipe($rectorRecipe);
        $templateVariables = $this->templateVariablesFactory->createFromConfiguration($configuration);

        // setup psr-4 autoload, if not already in
        $this->composerPackageAutoloadUpdater->processComposerAutoload($configuration);

        $templateFileInfos = $this->templateFinder->find($configuration);

        $targetDirectory = getcwd();

        $isUnwantedOverride = $this->overrideGuard->isUnwantedOverride(
            $templateFileInfos,
            $templateVariables,
            $configuration->getPackage(),
            $targetDirectory
        );
        if ($isUnwantedOverride) {
            $this->symfonyStyle->warning('No files were changed');

            return ShellCode::SUCCESS;
        }

        $generatedFilePaths = $this->fileGenerator->generateFiles(
            $templateFileInfos,
            $templateVariables,
            $configuration,
            $targetDirectory
        );

        $testCaseDirectoryPath = $this->resolveTestCaseDirectoryPath($generatedFilePaths);

        $this->configFilesystem->appendRectorServiceToSet($configuration, $templateVariables);

        $this->printSuccess($configuration->getName(), $generatedFilePaths, $testCaseDirectoryPath);

        return ShellCode::SUCCESS;
    }

    /**
     * @param string[] $generatedFilePaths
     */
    private function printSuccess(string $name, array $generatedFilePaths, string $testCaseFilePath): void
    {
        $message = sprintf('New files generated for "%s":', $name);
        $this->symfonyStyle->title($message);

        sort($generatedFilePaths);

        foreach ($generatedFilePaths as $generatedFilePath) {
            $fileInfo = new SmartFileInfo($generatedFilePath);
            $relativeFilePath = $fileInfo->getRelativeFilePathFromCwd();
            $this->symfonyStyle->writeln(' * ' . $relativeFilePath);
        }

        $message = sprintf('Make tests green again:%svendor/bin/phpunit %s', PHP_EOL . PHP_EOL, $testCaseFilePath);

        $this->symfonyStyle->success($message);
    }

    /**
     * @param string[] $generatedFilePaths
     */
    private function resolveTestCaseDirectoryPath(array $generatedFilePaths): string
    {
        foreach ($generatedFilePaths as $generatedFilePath) {
            if (! Strings::endsWith($generatedFilePath, 'Test.php')) {
                continue;
            }

            $generatedFileInfo = new SmartFileInfo($generatedFilePath);
            return dirname($generatedFileInfo->getRelativeFilePathFromCwd());
        }

        throw new ShouldNotHappenException();
    }
}
