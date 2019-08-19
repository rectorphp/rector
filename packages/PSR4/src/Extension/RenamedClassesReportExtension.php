<?php declare(strict_types=1);

namespace Rector\PSR4\Extension;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\Extension\ReportingExtensionInterface;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PSR4\Collector\RenamedClassesCollector;
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
        if ($this->renamedClassesCollector->getRenamedClasses() === []) {
            return;
        }

        $data = [
            # rector.yaml
            'services' => [
                RenameClassRector::class => [
                    '$oldToNewClasses' => $this->renamedClassesCollector->getRenamedClasses(),
                ],
            ],
        ];

        // 1.
        $yaml = Yaml::dump($data, 5);
        FileSystem::write(getcwd() . '/rename-classes-rector.yaml', $yaml);

        $nodes = [];

        $renamedClasses = $this->sortInterfaceFirst($this->renamedClassesCollector->getRenamedClasses());
        foreach ($renamedClasses as $oldClass => $newClass) {
            $classAlias = new FuncCall(new Name('class_alias'));
            $classAlias->args[] = new Arg(new String_($newClass));
            $classAlias->args[] = new Arg(new String_($oldClass));

            $nodes[] = new Expression($classAlias);
        }

        // 2.
        $aliasesContent = '<?php' . PHP_EOL . PHP_EOL . $this->betterStandardPrinter->print($nodes);
        FileSystem::write(getcwd() . '/rename-classes-aliases.php', $aliasesContent);

        // @todo shell exec!?
        $this->symfonyStyle->warning(
            'Run: "vendor/bin/rector process tests --config rename-classes-rector.yaml --autoload-file rename-classes-aliases.php" to finish the process'
        );
    }

    /**
     * Interfaces needs to be aliased first, as aliased class needs to have an aliased interface that implements.
     * PHP will crash with not very helpful "missing interface" error otherwise.
     *
     * Also abstract classes and traits, as other code depens on them.
     *
     * @param string[] $types
     * @return string[]
     */
    private function sortInterfaceFirst(array $types): array
    {
        $interfaces = [];
        $abstractClasses = [];
        $traits = [];
        $classes = [];

        foreach ($types as $oldType => $newType) {
            if (Strings::endsWith($oldType, 'Interface')) {
                $interfaces[$oldType] = $newType;
            } elseif (Strings::contains($oldType, 'Abstract')) {
                $abstractClasses[$oldType] = $newType;
            } elseif (Strings::contains($oldType, 'Trait')) {
                $traits[$oldType] = $newType;
            } else {
                $classes[$oldType] = $newType;
            }
        }

        return array_merge($interfaces, $traits, $abstractClasses, $classes);
    }
}
