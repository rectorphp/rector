<?php

declare(strict_types=1);

namespace Rector\Utils\RectorGenerator\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\Utils\RectorGenerator\Configuration\ConfigurationFactory;
use Rector\Utils\RectorGenerator\Contract\ContributorCommandInterface;
use Rector\Utils\RectorGenerator\TemplateVariablesFactory;
use Rector\Utils\RectorGenerator\ValueObject\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CreateRectorCommand extends Command implements ContributorCommandInterface
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

    public function __construct(
        SymfonyStyle $symfonyStyle,
        ConfigurationFactory $configurationFactory,
        FinderSanitizer $finderSanitizer,
        TemplateVariablesFactory $templateVariablesFactory
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->configurationFactory = $configurationFactory;
        $this->finderSanitizer = $finderSanitizer;
        $this->templateVariablesFactory = $templateVariablesFactory;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Create a new Rector, in proper location, with new tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->configurationFactory->createFromConfigFile(getcwd() . '/create-rector.yaml');
        $templateVariables = $this->templateVariablesFactory->createFromConfiguration($configuration);

        // setup psr-4 autoload, if not already in
        $this->processComposerAutoload($templateVariables);

        foreach ($this->findTemplateFileInfos() as $smartFileInfo) {
            $destination = $this->resolveDestination($smartFileInfo, $templateVariables, $configuration);

            $content = $this->resolveContent($smartFileInfo, $templateVariables);

            if ($configuration->getPackage() === 'Rector') {
                $content = Strings::replace($content, '#Rector\\\\Rector\\\\#ms', 'Rector\\');
                $content = Strings::replace(
                    $content,
                    '#use Rector\\\\AbstractRector;#',
                    'use Rector\\Rector\\AbstractRector;'
                );
            }

            FileSystem::write($destination, $content);

            $this->generatedFiles[] = $destination;

            if (Strings::endsWith($destination, 'Test.php')) {
                $this->testCasePath = dirname($destination);
            }
        }

        $this->appendToLevelConfig($configuration, $templateVariables);

        $this->printSuccess($configuration->getName());

        return ShellCode::SUCCESS;
    }

    /**
     * @param mixed[] $templateVariables
     */
    private function processComposerAutoload(array $templateVariables): void
    {
        $composerJsonFilePath = getcwd() . '/composer.json';
        $composerJson = $this->loadFileToJson($composerJsonFilePath);

        $package = $templateVariables['_Package_'];

        // skip core, already autoloaded
        if ($package === 'Rector') {
            return;
        }

        $namespace = 'Rector\\' . $package . '\\';
        $namespaceTest = 'Rector\\' . $package . '\\Tests\\';

        // already autoloaded?
        if (isset($composerJson['autoload']['psr-4'][$namespace])) {
            return;
        }

        $composerJson['autoload']['psr-4'][$namespace] = 'packages/' . $package . '/src';
        $composerJson['autoload-dev']['psr-4'][$namespaceTest] = 'packages/' . $package . '/tests';

        $this->saveJsonToFile($composerJsonFilePath, $composerJson);

        // rebuild new namespace
        $composerDumpProcess = new Process(['composer', 'dump']);
        $composerDumpProcess->run();
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
    private function appendToLevelConfig(Configuration $configuration, array $templateVariables): void
    {
        if ($configuration->getLevelConfig() === null) {
            return;
        }

        if (! file_exists($configuration->getLevelConfig())) {
            return;
        }

        $rectorFqnName = $this->applyVariables(self::RECTOR_FQN_NAME_PATTERN, $templateVariables);

        $levelConfigContent = FileSystem::read($configuration->getLevelConfig());

        // already added
        if (Strings::contains($levelConfigContent, $rectorFqnName)) {
            return;
        }

        $levelConfigContent = trim($levelConfigContent) . sprintf(
            '%s%s: ~%s',
            PHP_EOL,
            Strings::indent($rectorFqnName, 4, ' '),
            PHP_EOL
        );

        FileSystem::write($configuration->getLevelConfig(), $levelConfigContent);
    }

    private function printSuccess(string $name): void
    {
        $this->symfonyStyle->title(sprintf('New files generated for "%s"', $name));
        sort($this->generatedFiles);
        $this->symfonyStyle->listing($this->generatedFiles);

        $this->symfonyStyle->success(sprintf(
            'Now make these tests green again:%svendor/bin/phpunit %s',
            PHP_EOL . PHP_EOL,
            $this->testCasePath
        ));
    }

    /**
     * @return mixed[]
     */
    private function loadFileToJson(string $filePath): array
    {
        $fileContent = FileSystem::read($filePath);
        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @param mixed[] $json
     */
    private function saveJsonToFile(string $filePath, array $json): void
    {
        $content = Json::encode($json, Json::PRETTY);
        $content = $this->inlineSections($content, ['keywords', 'bin']);
        $content = $this->inlineAuthors($content);

        // make sure there is newline in the end
        $content = trim($content) . PHP_EOL;

        FileSystem::write($filePath, $content);
    }

    /**
     * @param mixed[] $variables
     */
    private function applyVariables(string $content, array $variables): string
    {
        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    /**
     * @param string[] $sections
     */
    private function inlineSections(string $jsonContent, array $sections): string
    {
        foreach ($sections as $section) {
            $pattern = '#("' . preg_quote($section, '#') . '": )\[(.*?)\](,)#ms';
            $jsonContent = Strings::replace($jsonContent, $pattern, function (array $match): string {
                $inlined = Strings::replace($match[2], '#\s+#', ' ');
                $inlined = trim($inlined);
                $inlined = '[' . $inlined . ']';
                return $match[1] . $inlined . $match[3];
            });
        }

        return $jsonContent;
    }

    private function inlineAuthors(string $jsonContent): string
    {
        $pattern = '#(?<start>"authors": \[\s+)(?<content>.*?)(?<end>\s+\](,))#ms';
        $jsonContent = Strings::replace($jsonContent, $pattern, function (array $match): string {
            $inlined = Strings::replace($match['content'], '#\s+#', ' ');
            $inlined = trim($inlined);
            $inlined = Strings::replace($inlined, '#},#', "},\n       ");

            return $match['start'] . $inlined . $match['end'];
        });

        return $jsonContent;
    }
}
