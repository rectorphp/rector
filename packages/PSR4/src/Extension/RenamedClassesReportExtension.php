<?php declare(strict_types=1);

namespace Rector\PSR4\Extension;

use Nette\Utils\FileSystem;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\Extension\ReportingExtensionInterface;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\PSR4\ValueObject\ClassRenameValueObject;
use Rector\Rector\Class_\RenameClassRector;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

final class RenamedClassesReportExtension implements ReportingExtensionInterface
{
    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        RenamedClassesCollector $renamedClassesCollector,
        SymfonyStyle $symfonyStyle,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->symfonyStyle = $symfonyStyle;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function run(): void
    {
        if (! $this->renamedClassesCollector->hasClassRenames()) {
            return;
        }

        // 1. dump rector config for RenameClassRector
        $rectorYamlContent = $this->createRectorYamlContent();
        FileSystem::write(getcwd() . DIRECTORY_SEPARATOR . 'renames-rector.yaml', $rectorYamlContent);

        // 2. dump class aliases
        $renameClassesAliasesContent = $this->createRenameClassAliasContent();
        FileSystem::write(getcwd() . DIRECTORY_SEPARATOR . 'class-aliases.php', $renameClassesAliasesContent);

        // 3. tell user what to do next
        $this->symfonyStyle->warning(
            'Now rename classes: "vendor/bin/rector process src tests -c renames-rector.yaml -a class-aliases.php"'
        );
    }

    /**
     * @param ClassRenameValueObject[] $classRenames
     * @return Expression[]
     */
    private function createClassAliasNodes(array $classRenames): array
    {
        $nodes = [];
        foreach ($classRenames as $classRename) {
            $classAlias = new FuncCall(new Name('class_alias'));
            $classAlias->args[] = new Arg(new String_($classRename->getNewClass()));
            $classAlias->args[] = new Arg(new String_($classRename->getOldClass()));

            $nodes[] = new Expression($classAlias);
        }

        return $nodes;
    }

    private function createRectorYamlContent(): string
    {
        $oldToNewClasses = $this->renamedClassesCollector->getOldToNewClassesSortedByHighestParentsAsString();

        $data = [
            # rector.yaml
            'services' => [
                RenameClassRector::class => [
                    '$oldToNewClasses' => $oldToNewClasses,
                ],
            ],
        ];

        return Yaml::dump($data, 10);
    }

    private function createRenameClassAliasContent(): string
    {
        $classRenames = $this->renamedClassesCollector->getClassRenamesSortedByHighestParents();

        $classAliasContent = $this->betterStandardPrinter->print($this->createClassAliasNodes($classRenames));

        return '<?php' . PHP_EOL . PHP_EOL . $classAliasContent;
    }
}
