<?php

declare(strict_types=1);

namespace Rector\Utils\RectorGenerator\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Utils\RectorGenerator\Composer\ComposerPackageAutoloadUpdater;
use Rector\Utils\RectorGenerator\Configuration\ConfigurationFactory;
use Rector\Utils\RectorGenerator\TemplateVariablesFactory;
use Rector\Utils\RectorGenerator\ValueObject\Configuration;
use Rector\Utils\RectorGenerator\ValueObject\Package;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CreateRectorCommand extends Command
{
    /**
     * @var string
     */
    private const TEMPLATES_DIRECTORY = __DIR__ . '/../../templates';

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
     * @var FinderSanitizer
     */
    private $finderSanitizer;

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
     * @param mixed[] $rectorRecipe
     */
    public function __construct(
        SymfonyStyle $symfonyStyle,
        ConfigurationFactory $configurationFactory,
        FinderSanitizer $finderSanitizer,
        TemplateVariablesFactory $templateVariablesFactory,
        ComposerPackageAutoloadUpdater $composerPackageAutoloadUpdater,
        array $rectorRecipe
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->configurationFactory = $configurationFactory;
        $this->finderSanitizer = $finderSanitizer;
        $this->templateVariablesFactory = $templateVariablesFactory;
        $this->rectorRecipe = $rectorRecipe;
        $this->composerPackageAutoloadUpdater = $composerPackageAutoloadUpdater;
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

        $templateFileInfos = $this->findTemplateFileInfos();
        $isUnwantedOverride = $this->isUnwantedOverride($templateFileInfos, $templateVariables, $configuration);

        if ($isUnwantedOverride) {
            $this->symfonyStyle->warning(
                'The rule already exists and you decided to keep the original. No files were changed'
            );
            return ShellCode::SUCCESS;
        }

        foreach ($this->findTemplateFileInfos() as $smartFileInfo) {
            $destination = $this->resolveDestination($smartFileInfo, $templateVariables, $configuration);

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
            $destination = $this->resolveDestination($templateFileInfo, $templateVariables, $configuration);

            if (! file_exists($destination)) {
                continue;
            }

            return ! $this->symfonyStyle->confirm('Files for this rules already exist. Should we override them?');
        }

        return false;
    }

    /**
     * @return SmartFileInfo[]
     */
    private function findTemplateFileInfos(): array
    {
        $finder = Finder::create()->files()
            ->in(self::TEMPLATES_DIRECTORY);

        return $this->finderSanitizer->sanitize($finder);
    }

    /**
     * @param string[] $templateVariables
     */
    private function resolveDestination(
        SmartFileInfo $smartFileInfo,
        array $templateVariables,
        Configuration $configuration
    ): string {
        $destination = $smartFileInfo->getRelativeFilePathFromDirectory(self::TEMPLATES_DIRECTORY);

        // normalize core package
        if ($configuration->getPackage() === 'Rector') {
            $destination = Strings::replace($destination, '#packages\/_Package_/tests/Rector#', 'tests/Rector');
            $destination = Strings::replace($destination, '#packages\/_Package_/src/Rector#', 'src/Rector');
        }

        // special keyword for 3rd party Rectors, not for core Github contribution
        if ($configuration->getPackage() === Package::UTILS) {
            $destination = Strings::replace($destination, '#packages\/_Package_#', 'utils/rector');
        }

        if (! Strings::match($destination, '#fixture[\d+]*\.php\.inc#')) {
            $destination = rtrim($destination, '.inc');
        }

        return $this->applyVariables($destination, $templateVariables);
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
