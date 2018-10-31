<?php declare(strict_types=1);

namespace Rector\ContributorTools\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\CodingStyle\AfterRectorCodingStyle;
use Rector\Console\ConsoleStyle;
use Rector\ContributorTools\Configuration\Configuration;
use Rector\ContributorTools\Configuration\ConfigurationFactory;
use Rector\ContributorTools\TemplateVariablesFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\FileSystem\FinderSanitizer;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use function Safe\getcwd;
use function Safe\sort;
use function Safe\sprintf;

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
     * @var ConsoleStyle
     */
    private $consoleStyle;

    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;

    /**
     * @var AfterRectorCodingStyle
     */
    private $afterRectorCodingStyle;

    /**
     * @var TemplateVariablesFactory
     */
    private $templateVariablesFactory;

    public function __construct(
        ConsoleStyle $consoleStyle,
        ConfigurationFactory $configurationFactory,
        FinderSanitizer $finderSanitizer,
        AfterRectorCodingStyle $afterRectorCodingStyle,
        TemplateVariablesFactory $templateVariablesFactory
    ) {
        parent::__construct();
        $this->consoleStyle = $consoleStyle;
        $this->configurationFactory = $configurationFactory;
        $this->finderSanitizer = $finderSanitizer;
        $this->afterRectorCodingStyle = $afterRectorCodingStyle;
        $this->templateVariablesFactory = $templateVariablesFactory;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Create a new Rector, in proper location, with new tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->configurationFactory->createFromConfigFile(getcwd() . '/create-rector.yml');
        $templateVariables = $this->templateVariablesFactory->createFromConfiguration($configuration);

        foreach ($this->findTemplateFileInfos() as $smartFileInfo) {
            $destination = $this->resolveDestination($smartFileInfo, $templateVariables);
            $content = $this->resolveContent($smartFileInfo, $templateVariables);
            FileSystem::write($destination, $content);

            $this->generatedFiles[] = $destination;

            if (Strings::endsWith($destination, 'Test.php')) {
                $this->testCasePath = dirname($destination);
            }
        }

        $this->appendToLevelConfig($configuration, $templateVariables);

        $this->applyCodingStyle();
        $this->printSuccess($configuration->getName());

        return ShellCode::SUCCESS;
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
    private function resolveDestination(SmartFileInfo $smartFileInfo, array $templateVariables): string
    {
        $destination = $smartFileInfo->getRelativeFilePathFromDirectory(self::TEMPLATES_DIRECTORY);
        if (! Strings::match($destination, '#(wrong|correct)[\d+]*\.php\.inc#')) {
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
        if (! $configuration->getLevelConfig()) {
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

    private function applyCodingStyle(): void
    {
        // filter only .php files
        $generatedPhpFiles = array_filter($this->generatedFiles, function (string $file) {
            return Strings::endsWith($file, '.php');
        });

        $this->afterRectorCodingStyle->apply($generatedPhpFiles);
    }

    private function printSuccess(string $name): void
    {
        $this->consoleStyle->title(sprintf('New files generated for "%s"', $name));
        sort($this->generatedFiles);
        $this->consoleStyle->listing($this->generatedFiles);

        $this->consoleStyle->success(sprintf(
            'Now make these tests green again:%svendor/bin/phpunit %s',
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
}
