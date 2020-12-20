<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Command;

use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\RectorGenerator\Composer\ComposerPackageAutoloadUpdater;
use Rector\RectorGenerator\Config\ConfigFilesystem;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\Generator\FileGenerator;
use Rector\RectorGenerator\Guard\OverrideGuard;
use Rector\RectorGenerator\Provider\NodeTypesProvider;
use Rector\RectorGenerator\Provider\PackageNameProvider;
use Rector\RectorGenerator\Provider\RectorRecipeProvider;
use Rector\RectorGenerator\Provider\SetListProvider;
use Rector\RectorGenerator\TemplateVariablesFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GenerateCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

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
     * @var FileGenerator
     */
    private $fileGenerator;

    /**
     * @var RectorRecipeProvider
     */
    private $rectorRecipeProvider;

    /**
     * @var PackageNameProvider
     */
    private $packageNameProvider;

    /**
     * @var NodeTypesProvider
     */
    private $nodeTypesProvider;

    /**
     * @var SetListProvider
     */
    private $setListProvider;

    public function __construct(
        ComposerPackageAutoloadUpdater $composerPackageAutoloadUpdater,
        ConfigFilesystem $configFilesystem,
        FileGenerator $fileGenerator,
        OverrideGuard $overrideGuard,
        SymfonyStyle $symfonyStyle,
        TemplateFinder $templateFinder,
        TemplateVariablesFactory $templateVariablesFactory,
        RectorRecipeProvider $rectorRecipeProvider,
        PackageNameProvider $packageNameProvider,
        NodeTypesProvider $nodeTypesProvider,
        SetListProvider $setListProvider
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->templateVariablesFactory = $templateVariablesFactory;
        $this->composerPackageAutoloadUpdater = $composerPackageAutoloadUpdater;
        $this->templateFinder = $templateFinder;
        $this->configFilesystem = $configFilesystem;
        $this->overrideGuard = $overrideGuard;
        $this->fileGenerator = $fileGenerator;
        $this->rectorRecipeProvider = $rectorRecipeProvider;
        $this->packageNameProvider = $packageNameProvider;
        $this->nodeTypesProvider = $nodeTypesProvider;
        $this->setListProvider = $setListProvider;
    }

    protected function configure(): void
    {
        $this->setAliases(['c', 'create', 'g']);
        $this->setDescription('[DEV] Create a new Rector, in a proper location, with new tests');
        $this->addOption(
            'interactive',
            'i',
            InputOption::VALUE_NONE,
            'Turns on Interactive Mode - Rector will be generated based on responses to questions instead of using rector-recipe.php',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rectorRecipe = $this->getRectorRecipe($input);

        $templateVariables = $this->templateVariablesFactory->createFromRectorRecipe($rectorRecipe);

        // setup psr-4 autoload, if not already in
        $this->composerPackageAutoloadUpdater->processComposerAutoload($rectorRecipe);

        $templateFileInfos = $this->templateFinder->find($rectorRecipe);

        $targetDirectory = getcwd();

        $isUnwantedOverride = $this->overrideGuard->isUnwantedOverride(
            $templateFileInfos,
            $templateVariables,
            $rectorRecipe,
            $targetDirectory
        );
        if ($isUnwantedOverride) {
            $this->symfonyStyle->warning('No files were changed');

            return ShellCode::SUCCESS;
        }

        $generatedFilePaths = $this->fileGenerator->generateFiles(
            $templateFileInfos,
            $templateVariables,
            $rectorRecipe,
            $targetDirectory
        );

        $this->configFilesystem->appendRectorServiceToSet($rectorRecipe, $templateVariables);
        $testCaseDirectoryPath = $this->resolveTestCaseDirectoryPath($generatedFilePaths);

        $this->printSuccess($rectorRecipe->getName(), $generatedFilePaths, $testCaseDirectoryPath);

        return ShellCode::SUCCESS;
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

    private function getRectorRecipe(InputInterface $input): RectorRecipe
    {
        if (!$input->getOption('interactive')) {
            return $this->rectorRecipeProvider->provide();
        }

        return $this->prepareRectorRecipe();
    }

    private function prepareRectorRecipe(): RectorRecipe
    {
        $rectorRecipe = new RectorRecipe(
            $this->askForPackageName(),
            $this->askForRectorName(),
            $this->askForNodeTypes(),
            $this->askForRectorDescription(),
            $this->getExampleCodeBefore(),
            $this->getExampleCodeAfter(),
        );
        $rectorRecipe->setResources($this->askForResources());

        $set = $this->askForSet();
        if ($set) {
            $rectorRecipe->setSet($this->askForSet());
        }

        return $rectorRecipe;
    }

    private function askForPackageName(): string
    {
        $question = new Question(sprintf('Package name for which Rector should be created (e.g. <fg=yellow>%s</>)', 'Naming'));
        $question->setAutocompleterValues($this->packageNameProvider->provide());

        $packageName = $this->symfonyStyle->askQuestion($question);

        return $packageName ?? $this->askForPackageName();
    }

    private function askForRectorName(): string
    {
        $rectorName = $this->symfonyStyle->ask(sprintf(
            'Class name of the Rector to create (e.g. <fg=yellow>%s</>)',
            'RenameMethodCallRector',
        ));

        return $rectorName ?? $this->askForRectorName();
    }

    /**
     * @return array<int, string>
     */
    private function askForNodeTypes(): array
    {
        $question = new ChoiceQuestion(sprintf(
            'For what Nodes should the Rector be run (e.g. <fg=yellow>%s</>)',
            'Expr/MethodCall',
        ), $this->nodeTypesProvider->provide());
        $question->setMultiselect(true);

        $nodeTypes = $this->symfonyStyle->askQuestion($question);

        $classes = [];
        foreach ($nodeTypes as $nodeType) {
            $name = 'PhpParser\Node\\' . str_replace('/', '\\', $nodeType);
            $classes[] = $name;
        }

        return $classes;
    }

    private function askForRectorDescription(): string
    {
        $description = $this->symfonyStyle->ask('Short description of new Rector');

        return $description ?? $this->askForRectorDescription();
    }

    private function getExampleCodeBefore(): string
    {
        return <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    public function run()
    {
        $this->something();
    }
}

CODE_SAMPLE;
    }

    private function getExampleCodeAfter(): string
    {
        return <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    public function run()
    {
        $this->somethingElse();
    }
}

CODE_SAMPLE;
    }

    private function askForResources(): array
    {
        $resources = [];

        while (true) {
            $resource = $this->symfonyStyle->ask(sprintf(
                'Link to resource that explains why the change is needed (e.g. <fg=yellow>%s</>)',
                'https://github.com/symfony/symfony/blob/704c648ba53be38ef2b0105c97c6497744fef8d8/UPGRADE-6.0.md',
            ));

            if ($resource === null) {
                break;
            }

            $resources[] = $resource;
        }

        return $resources;
    }

    private function askForSet(): ?string
    {
        $question = new Question(sprintf('Set to which Rector should be added (e.g. <fg=yellow>%s</>)', 'SYMFONY_52'));
        $question->setAutocompleterValues($this->setListProvider->provide());

        $setName = $this->symfonyStyle->askQuestion($question);
        if ($setName === null) {
            return null;
        }

        return constant('\Rector\Set\ValueObject\SetList::' . $setName);
    }
}
