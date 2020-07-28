<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Command;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Rector\RectorGenerator\Composer\ComposerPackageAutoloadUpdater;
use Rector\RectorGenerator\Config\ConfigFilesystem;
use Rector\RectorGenerator\Configuration\ConfigurationFactory;
use Rector\RectorGenerator\FileSystem\TemplateFileSystem;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\Guard\OverrideGuard;
use Rector\RectorGenerator\TemplateFactory;
use Rector\RectorGenerator\TemplateVariablesFactory;
use Rector\RectorGenerator\ValueObject\Configuration;
use Rector\RectorGenerator\ValueObject\RecipeOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class CreateRectorCommand extends Command
{
    /**
     * @var string
     */
    private $testCasePath;

    /**
     * @var string[]
     */
    private $generatedFiles = [];

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
     * @var TemplateFileSystem
     */
    private $templateFileSystem;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var ConfigFilesystem
     */
    private $configFilesystem;

    /**
     * @var OverrideGuard
     */
    private $overrideGuard;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        ConfigurationFactory $configurationFactory,
        TemplateVariablesFactory $templateVariablesFactory,
        ComposerPackageAutoloadUpdater $composerPackageAutoloadUpdater,
        TemplateFinder $templateFinder,
        TemplateFileSystem $templateFileSystem,
        TemplateFactory $templateFactory,
        ConfigFilesystem $configFilesystem,
        OverrideGuard $overrideGuard,
        SmartFileSystem $smartFileSystem,
        ParameterProvider $parameterProvider
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->configurationFactory = $configurationFactory;
        $this->templateVariablesFactory = $templateVariablesFactory;
        $this->composerPackageAutoloadUpdater = $composerPackageAutoloadUpdater;
        $this->templateFinder = $templateFinder;
        $this->templateFileSystem = $templateFileSystem;
        $this->templateFactory = $templateFactory;
        $this->configFilesystem = $configFilesystem;
        $this->overrideGuard = $overrideGuard;
        $this->smartFileSystem = $smartFileSystem;
        $this->parameterProvider = $parameterProvider;
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

        dump($templateFileInfos);
        die;

        $isUnwantedOverride = $this->overrideGuard->isUnwantedOverride(
            $templateFileInfos,
            $templateVariables,
            $configuration
        );

        if ($isUnwantedOverride) {
            $this->symfonyStyle->warning('No files were changed');

            return ShellCode::SUCCESS;
        }

//        $this->configFilesystem->appendRectorServiceToSet($configuration, $templateVariables);

        $this->generateFiles($templateFileInfos, $templateVariables, $configuration);

        $this->printSuccess($configuration->getName());

        return ShellCode::SUCCESS;
    }

    /**
     * @param SmartFileInfo[] $templateFileInfos
     */
    private function generateFiles(
        array $templateFileInfos,
        array $templateVariables,
        Configuration $configuration
    ): void {
        foreach ($templateFileInfos as $smartFileInfo) {
            $destination = $this->templateFileSystem->resolveDestination(
                $smartFileInfo,
                $templateVariables,
                $configuration
            );

            $content = $this->templateFactory->create($smartFileInfo->getContents(), $templateVariables);
            if ($configuration->getPackage() === RecipeOption::PACKAGE_CORE) {
                $content = $this->addOneMoreRectorNesting($content);
            }

            $this->smartFileSystem->dumpFile($destination, $content);

            $this->generatedFiles[] = $destination;

            // is a test case?
            if (Strings::endsWith($destination, 'Test.php')) {
                $this->testCasePath = dirname($destination);
            }
        }
    }

    private function printSuccess(string $name): void
    {
        $message = sprintf('New files generated for "%s"', $name);
        $this->symfonyStyle->title($message);
        sort($this->generatedFiles);
        $this->symfonyStyle->listing($this->generatedFiles);
        $message = sprintf(
            'Make tests green again:%svendor/bin/phpunit %s',
            PHP_EOL . PHP_EOL,
            $this->testCasePath
        );

        $this->symfonyStyle->success($message);
    }

    private function addOneMoreRectorNesting(string $content): string
    {
        $content = Strings::replace($content, '#Rector\\\\Rector\\\\#ms', 'Rector\\Core\\');

        return Strings::replace(
            $content,
            '#use Rector\\\\AbstractRector;#',
            'use Rector\\Core\\Rector\\AbstractRector;'
        );
    }
}
