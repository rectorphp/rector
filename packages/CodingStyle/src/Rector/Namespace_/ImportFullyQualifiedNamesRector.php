<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ImportFullyQualifiedNamesRector extends AbstractRector
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var string[]
     */
    private $newUseStatements = [];

    /**
     * @var string[]
     */
    private $alreadyImportedUses = [];

    /**
     * @var string[]
     */
    private $aliasedUses = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, DocBlockManipulator $docBlockManipulator)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Import fully qualified names to use statements', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function create()
    {
          return SomeAnother\AnotherClass;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use SomeAnother\AnotherClass;

class SomeClass
{
    public function create()
    {
          return AnotherClass;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->alreadyImportedUses = [];
        $this->newUseStatements = [];

        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($node, Class_::class);
        if ($class === null) {
            return null;
        }

        $this->resolveAlreadyImportedUses($node);
        $newUseStatements = $this->importNamesAndCollectNewUseStatements($class);

        $this->addNewUseStatements($node, $newUseStatements);

        return $node;
    }

    private function resolveAlreadyImportedUses(Namespace_ $namespace): void
    {
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->find($namespace->stmts, function (Node $node) {
            if (! $node instanceof Use_) {
                return false;
            }

            // only import uses
            return $node->type === Use_::TYPE_NORMAL;
        });

        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                $name = $this->getName($useUse);
                if ($name === null) {
                    throw new ShouldNotHappenException();
                }

                if ($useUse->alias !== null) {
                    // alias workaround
                    $this->aliasedUses[] = $name;
                }

                $this->alreadyImportedUses[] = $name;
            }
        }

        /** @var Class_ $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($namespace->stmts, Class_::class);

        // add class itself
        $className = $this->getName($class);
        if ($className === null) {
            return;
        }

        $this->alreadyImportedUses[] = $className;
    }

    /**
     * @param string[] $newUseStatements
     */
    private function addNewUseStatements(Namespace_ $namespace, array $newUseStatements): void
    {
        if ($newUseStatements === []) {
            return;
        }

        $newUses = [];

        foreach ($newUseStatements as $newUseStatement) {
            // already imported in previous cycle
            if (in_array($newUseStatement, $this->alreadyImportedUses, true)) {
                continue;
            }

            $useUse = new UseUse(new Name($newUseStatement));
            $newUses[] = new Use_([$useUse]);

            $this->alreadyImportedUses[] = $newUseStatement;
        }

        $namespace->stmts = array_merge($newUses, $namespace->stmts);
    }

    /**
     * @return string[]
     */
    private function importNamesAndCollectNewUseStatements(Class_ $class): array
    {
        if ($class->name === null) {
            return [];
        }

        $this->newUseStatements = [];

        $classShortName = $class->name->toString();

        $this->callableNodeTraverser->traverseNodesWithCallable([$class], function (Node $node) use ($classShortName) {
            if (! $node instanceof Name) {
                return null;
            }

            $name = $node->getAttribute('originalName');
            if ($name instanceof Name) {
                // already short
                if (! Strings::contains($name->toString(), '\\')) {
                    return null;
                }
            }

            // 0. name is same as class name → skip it
            if ($node->getLast() === $classShortName) {
                return null;
            }

            if ($this->getName($node) === $node->toString()) {
                $fullyQualifiedName = $this->getName($node);

                // the similar end is already imported → skip
                if ($this->shouldSkipName($fullyQualifiedName)) {
                    return null;
                }

                $shortName = $this->getShortName($fullyQualifiedName);
                if (isset($this->newUseStatements[$shortName])) {
                    if ($fullyQualifiedName === $this->newUseStatements[$shortName]) {
                        return new Name($shortName);
                    }

                    return null;
                }

                if (! in_array($fullyQualifiedName, $this->alreadyImportedUses, true)) {
                    $this->newUseStatements[$shortName] = $fullyQualifiedName;
                }

                // possibly aliased
                if (in_array($fullyQualifiedName, $this->aliasedUses, true)) {
                    return null;
                }

                return new Name($shortName);
            }
        });

        // for doc blocks
        $this->callableNodeTraverser->traverseNodesWithCallable([$class], function (Node $node): void {
            $importedDocUseStatements = $this->docBlockManipulator->importNames($node, $this->alreadyImportedUses);
            $this->newUseStatements = array_merge($this->newUseStatements, $importedDocUseStatements);
        });

        return $this->newUseStatements;
    }

    private function getShortName(string $fullyQualifiedName): string
    {
        return Strings::after($fullyQualifiedName, '\\', -1) ?: $fullyQualifiedName;
    }

    // 1. name is fully qualified → import it
    private function shouldSkipName(string $fullyQualifiedName): bool
    {
        // not namespaced class
        if (! Strings::contains($fullyQualifiedName, '\\')) {
            return true;
        }

        $shortName = $this->getShortName($fullyQualifiedName);

        // nothing to change
        if ($shortName === $fullyQualifiedName) {
            return true;
        }

        foreach ($this->alreadyImportedUses as $alreadyImportedUse) {
            $shortAlreadyImportedUsed = $this->getShortName($alreadyImportedUse);
            if ($alreadyImportedUse !== $fullyQualifiedName && $shortAlreadyImportedUsed === $shortName) {
                return true;
            }
        }

        return false;
    }
}
