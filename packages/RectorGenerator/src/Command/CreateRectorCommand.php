<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\RectorGenerator\Composer\ComposerPackageAutoloadUpdater;
use Rector\RectorGenerator\Configuration\ConfigurationFactory;
use Rector\RectorGenerator\FileSystem\TemplateFileSystem;
use Rector\RectorGenerator\Finder\TemplateFinder;
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
    private const RECTOR_FQN_NAME_PATTERN = 'Rector\_Package_\Rector\_Category_\_Name_';

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
     * @var mixed[]
     */
    private $rectorRecipe = [];

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
     * @param mixed[] $rectorRecipe
     */
    public function __construct(
        SymfonyStyle $symfonyStyle,
        ConfigurationFactory $configurationFactory,
        TemplateVariablesFactory $templateVariablesFactory,
        ComposerPackageAutoloadUpdater $composerPackageAutoloadUpdater,
        TemplateFinder $templateFinder,
        TemplateFileSystem $templateFileSystem,
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

        $templateFileInfos = $this->templateFinder->find($configuration->isPhpSnippet());
        $isUnwantedOverride = $this->isUnwantedOverride($templateFileInfos, $templateVariables, $configuration);

        if ($isUnwantedOverride) {
            $this->symfonyStyle->warning(
                'The rule already exists and you decided to keep the original. No files were changed'
            );
            return ShellCode::SUCCESS;
        }

        foreach ($templateFileInfos as $smartFileInfo) {
            $destination = $this->templateFileSystem->resolveDestination(
                $smartFileInfo,
                $templateVariables,
                $configuration
            );

            $content = $this->resolveContent($smartFileInfo, $templateVariables);

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

        $this->appendRectorServiceToSetConfig($configuration, $templateVariables);

        $this->printSuccess($configuration->getName());

        return ShellCode::SUCCESS;
    }

    /**
     * @param SmartFileInfo[] $templateFileInfos
     * @param mixed[] $templateVariables
     */
    private function isUnwantedOverride(
        array $templateFileInfos,
        array $templateVariables,
        Configuration $configuration
    ) {
        foreach ($templateFileInfos as $templateFileInfo) {
            $destination = $this->templateFileSystem->resolveDestination(
                $templateFileInfo,
                $templateVariables,
                $configuration
            );

            if (! file_exists($destination)) {
                continue;
            }

            return ! $this->symfonyStyle->confirm('Files for this rules already exist. Should we override them?');
        }

        return false;
    }

    /**
     * @param string[] $templateVariables
     */
    private function resolveContent(SmartFileInfo $smartFileInfo, array $templateVariables): string
    {
        return $this->applyVariables($smartFileInfo->getContents(), $templateVariables);
    }

    /**
     * @param string[] $templateVariables
     */
    private function appendRectorServiceToSetConfig(Configuration $configuration, array $templateVariables): void
    {
        if ($configuration->getSetConfig() === null) {
            return;
        }

        if (! file_exists($configuration->getSetConfig())) {
            return;
        }

        $rectorFqnName = $this->applyVariables(self::RECTOR_FQN_NAME_PATTERN, $templateVariables);

        $setConfigContent = FileSystem::read($configuration->getSetConfig());

        // already added
        if (Strings::contains($setConfigContent, $rectorFqnName)) {
            return;
        }

        $setConfigContent = trim($setConfigContent) . sprintf(
            '%s%s: null%s',
            PHP_EOL,
            $this->indentFourSpaces($rectorFqnName),
            PHP_EOL
        );

        FileSystem::write($configuration->getSetConfig(), $setConfigContent);
    }

    private function printSuccess(string $name): void
    {
        $this->symfonyStyle->title(sprintf('New files generated for "%s"', $name));
        sort($this->generatedFiles);
        $this->symfonyStyle->listing($this->generatedFiles);

        $this->symfonyStyle->success(sprintf(
            'Now make these tests green:%svendor/bin/phpunit %s',
            PHP_EOL . PHP_EOL,
            $this->testCasePath
        ));
    }

    /**
     * @param mixed[] $variables
     */
    private function applyVariables(string $content, array $variables): string
    {
        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    private function addOneMoreRectorNesting(string $content): string
    {
        $content = Strings::replace($content, '#Rector\\\\Rector\\\\#ms', 'Rector\\');

        return Strings::replace(
            $content,
            '#use Rector\\\\AbstractRector;#',
            'use Rector\\Rector\\AbstractRector;'
        );
    }

    private function indentFourSpaces(string $string): string
    {
        return Strings::indent($string, 4, ' ');
    }
}
