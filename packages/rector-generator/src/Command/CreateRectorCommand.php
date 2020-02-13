<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\RectorGenerator\Composer\ComposerPackageAutoloadUpdater;
use Rector\RectorGenerator\Config\ConfigFilesystem;
use Rector\RectorGenerator\Configuration\ConfigurationFactory;
use Rector\RectorGenerator\FileSystem\TemplateFileSystem;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\Guard\OverrideGuard;
use Rector\RectorGenerator\TemplateFactory;
use Rector\RectorGenerator\TemplateVariablesFactory;
use Rector\RectorGenerator\ValueObject\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;

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
     * @var mixed[]
     */
    private $rectorRecipe = [];

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
     * @param mixed[] $rectorRecipe
     */
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
        array $rectorRecipe
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->configurationFactory = $configurationFactory;
        $this->templateVariablesFactory = $templateVariablesFactory;
        $this->rectorRecipe = $rectorRecipe;
        $this->composerPackageAutoloadUpdater = $composerPackageAutoloadUpdater;
        $this->templateFinder = $templateFinder;
        $this->templateFileSystem = $templateFileSystem;
        $this->templateFactory = $templateFactory;
        $this->configFilesystem = $configFilesystem;
        $this->overrideGuard = $overrideGuard;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setAliases(['c']);
        $this->setDescription('[Dev] Create a new Rector, in a proper location, with new tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->configurationFactory->createFromRectorRecipe($this->rectorRecipe);
        $templateVariables = $this->templateVariablesFactory->createFromConfiguration($configuration);

        // setup psr-4 autoload, if not already in
        $this->composerPackageAutoloadUpdater->processComposerAutoload($configuration);

        $templateFileInfos = $this->templateFinder->find($configuration);

        $isUnwantedOverride = $this->overrideGuard->isUnwantedOverride(
            $templateFileInfos,
            $templateVariables,
            $configuration
        );

        if ($isUnwantedOverride) {
            $this->symfonyStyle->warning(
                'The rule already exists and you decided to keep the original. No files were changed'
            );
            return ShellCode::SUCCESS;
        }

        $this->generateFiles($templateFileInfos, $templateVariables, $configuration);

        $this->configFilesystem->appendRectorServiceToSet($configuration, $templateVariables);

        $this->printSuccess($configuration->getName());

        return ShellCode::SUCCESS;
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
            if ($configuration->getPackage() === 'Rector') {
                $content = $this->addOneMoreRectorNesting($content);
            }

            FileSystem::write($destination, $content);

            $this->generatedFiles[] = $destination;

            // is a test case?
            if (Strings::endsWith($destination, 'Test.php')) {
                $this->testCasePath = dirname($destination);
            }
        }
    }

    private function printSuccess(string $name): void
    {
        $this->symfonyStyle->title(sprintf('New files generated for "%s"', $name));
        sort($this->generatedFiles);
        $this->symfonyStyle->listing($this->generatedFiles);

        $this->symfonyStyle->success(sprintf(
            'Make tests green again:%svendor/bin/phpunit %s',
            PHP_EOL . PHP_EOL,
            $this->testCasePath
        ));
    }
}
