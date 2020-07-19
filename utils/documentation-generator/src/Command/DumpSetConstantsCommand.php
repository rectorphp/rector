<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\Command;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\Console\Command\AbstractCommand;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Utils\DocumentationGenerator\DataProvider\SetNamesProvider;
use Rector\Utils\DocumentationGenerator\NodeFactory\SetClassNodeFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class DumpSetConstantsCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private const SET_FILE_PATH = __DIR__ . '/../../../../src/ValueObject/Set.php';

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var SetNamesProvider
     */
    private $setNamesProvider;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var SetClassNodeFactory
     */
    private $setClassNodeFactory;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        SetNamesProvider $setNamesProvider,
        SymfonyStyle $symfonyStyle,
        SmartFileSystem $smartFileSystem,
        SetClassNodeFactory $setClassNodeFactory
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->setNamesProvider = $setNamesProvider;
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
        $this->setClassNodeFactory = $setClassNodeFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[DOCS] Dump class with set constants');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $constantNamesToSetNames = $this->setNamesProvider->provide();

        $class = $this->setClassNodeFactory->createFromConstantNamesToSetNames($constantNamesToSetNames);
        $namespace = $this->createNamespace($class);
        $fileStmts = [$this->createDeclareStrictTypes(), new Nop(), $namespace];

        $this->printFileStmtsToSetFile($fileStmts);

        $this->reportSuccess();

        return ShellCode::SUCCESS;
    }

    private function createDeclareStrictTypes(): Declare_
    {
        $declareDeclare = new DeclareDeclare('strict_types', new LNumber(1));
        return new Declare_([$declareDeclare]);
    }

    private function createNamespace(Class_ $class): Namespace_
    {
        $namespace = new Namespace_(new Name('Rector\Core\ValueObject'));
        $namespace->stmts[] = $class;

        return $namespace;
    }

    /**
     * @param Node[] $fileStmts
     */
    private function printFileStmtsToSetFile(array $fileStmts): void
    {
        $contents = $this->betterStandardPrinter->prettyPrintFile($fileStmts);
        $this->smartFileSystem->dumpFile(self::SET_FILE_PATH, $contents);
    }

    private function reportSuccess(): void
    {
        $setFileInfo = new SmartFileInfo(self::SET_FILE_PATH);

        $successMessage = sprintf('New "Set" class was dumped to "%s"', $setFileInfo->getRelativeFilePathFromCwd());
        $this->symfonyStyle->success($successMessage);
    }
}
