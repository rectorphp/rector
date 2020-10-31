<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Command;

use Rector\Core\Configuration\Option;
use Rector\Core\Console\Command\AbstractCommand;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\DocumentationGenerator\Printer\RectorsDocumentationPrinter;
use Rector\Testing\Finder\RectorsFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class DumpRectorsCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private const OUTPUT_FILE = 'output-file';

    /**
     * @var RectorsFinder
     */
    private $rectorsFinder;

    /**
     * @var RectorsDocumentationPrinter
     */
    private $rectorsDocumentationPrinter;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        RectorsDocumentationPrinter $rectorsDocumentationPrinter,
        RectorsFinder $rectorsFinder,
        SymfonyStyle $symfonyStyle,
        SmartFileSystem $smartFileSystem
    ) {
        parent::__construct();

        $this->rectorsFinder = $rectorsFinder;
        $this->rectorsDocumentationPrinter = $rectorsDocumentationPrinter;
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[DOCS] Dump overview of all Rectors');

        $this->addArgument(
            Option::SOURCE,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Directories with Rector rules'
        );

        $this->addOption(
            self::OUTPUT_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Where to output the file',
            getcwd() . '/docs/rector_rules_overview.md'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = (array) $input->getArgument(Option::SOURCE);
        $isRectorProject = $source === [];

        $rectors = $this->findRectorClassesInDirectoriesAndCreateRectorObjects($isRectorProject, $source);

        $outputFile = (string) $input->getOption(self::OUTPUT_FILE);
        $printedContent = $this->rectorsDocumentationPrinter->print($rectors, $isRectorProject);
        $this->smartFileSystem->dumpFile($outputFile, $printedContent);

        $outputFileFileInfo = new SmartFileInfo($outputFile);
        $message = sprintf(
            'Documentation for "%d" Rector rules was generated to "%s"',
            count($rectors),
            $outputFileFileInfo->getRelativeFilePathFromCwd()
        );
        $this->symfonyStyle->success($message);

        return ShellCode::SUCCESS;
    }

    /**
     * @param string[] $source
     * @return RectorInterface[]
     */
    private function findRectorClassesInDirectoriesAndCreateRectorObjects(bool $isRectorProject, array $source): array
    {
        if ($isRectorProject) {
            // fallback to core Rectors
            return $this->rectorsFinder->findInDirectoriesAndCreate([
                __DIR__ . '/../../../../rules',
                __DIR__ . '/../../../../packages',
            ]);
        }

        // custom directory
        return $this->rectorsFinder->findInDirectoriesAndCreate($source);
    }
}
