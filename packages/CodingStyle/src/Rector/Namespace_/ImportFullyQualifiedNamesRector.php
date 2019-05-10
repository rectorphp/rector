<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Imports\ImportsInClassCollection;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    private $alreadyUsedShortNames = [];

    /**
     * @var string[]
     */
    private $newUseStatements = [];

    /**
     * @var string[]
     */
    private $newFunctionUseStatements = [];

    /**
     * @var string[]
     */
    private $aliasedUses = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var ImportsInClassCollection
     */
    private $importsInClassCollection;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var bool
     */
    private $shouldImportDocBlocks = true;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        DocBlockManipulator $docBlockManipulator,
        ImportsInClassCollection $importsInClassCollection,
        ClassNaming $classNaming,
        bool $shouldImportDocBlocks = true
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->docBlockManipulator = $docBlockManipulator;
        $this->importsInClassCollection = $importsInClassCollection;
        $this->classNaming = $classNaming;
        $this->shouldImportDocBlocks = $shouldImportDocBlocks;
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
        $this->resetCollectedNames();

        $this->resolveAlreadyImportedUses($node);

        // "new X" or "X::static()"
        $this->resolveAlreadyUsedShortNames($node);

        $newUseStatements = $this->importNamesAndCollectNewUseStatements($node);
        $this->addNewUseStatements($node, $newUseStatements);

        return $node;
    }

    private function resolveAlreadyImportedUses(Namespace_ $namespace): void
    {
        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($namespace->stmts, Class_::class);

        // add class itself
        if ($class !== null) {
            $className = $this->getName($class);
            if ($className !== null) {
                $this->importsInClassCollection->addImport($className);
            }
        }

        $this->callableNodeTraverser->traverseNodesWithCallable($namespace->stmts, function (Node $node) {
            if (! $node instanceof Use_) {
                return null;
            }

            // only import uses
            if ($node->type !== Use_::TYPE_NORMAL) {
                return null;
            }

            foreach ($node->uses as $useUse) {
                $name = $this->getName($useUse);
                if ($name === null) {
                    throw new ShouldNotHappenException();
                }

                if ($useUse->alias !== null) {
                    // alias workaround
                    $this->aliasedUses[] = $name;
                }

                $this->importsInClassCollection->addImport($name);
            }
        });
    }

    /**
     * @param string[] $newUseStatements
     */
    private function addNewUseStatements(Namespace_ $namespace, array $newUseStatements): void
    {
        if ($newUseStatements === [] && $this->newFunctionUseStatements === []) {
            return;
        }

        $newUses = [];
        $newUseStatements = array_unique($newUseStatements);

        $namespaceName = $this->getName($namespace);
        if ($namespaceName === null) {
            throw new ShouldNotHappenException();
        }

        foreach ($newUseStatements as $newUseStatement) {
            if ($this->isCurrentNamespace($namespaceName, $newUseStatement)) {
                continue;
            }

            // already imported in previous cycle
            $useUse = new UseUse(new Name($newUseStatement));
            $newUses[] = new Use_([$useUse]);

            $this->importsInClassCollection->addImport($newUseStatement);
        }

        foreach ($this->newFunctionUseStatements as $newFunctionUseStatement) {
            if ($this->isCurrentNamespace($namespaceName, $newFunctionUseStatement)) {
                continue;
            }

            // already imported in previous cycle
            $useUse = new UseUse(new Name($newFunctionUseStatement), null, Use_::TYPE_FUNCTION);
            $newUses[] = new Use_([$useUse]);

            $this->importsInClassCollection->addImport($newFunctionUseStatement);
        }

        $namespace->stmts = array_merge($newUses, $namespace->stmts);
    }

    /**
     * @return string[]
     */
    private function importNamesAndCollectNewUseStatements(Namespace_ $node): array
    {
        $this->newUseStatements = [];
        $this->newFunctionUseStatements = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node->stmts, function (Node $node): ?Name {
            if (! $node instanceof Name) {
                return null;
            }

            $name = $node->getAttribute('originalName');
            if ($name instanceof Name) {
                // already short
                if (! Strings::contains($name->toString(), '\\')) {
                    return null;
                }
            } else {
                return null;
            }

            // the short name is already used, skip it
            $shortName = $this->classNaming->getShortName($name->toString());

            // is already used
            if (isset($this->alreadyUsedShortNames[$shortName]) && $this->alreadyUsedShortNames[$shortName] !== $name->toString()) {
                return null;
            }

            if ($this->getName($node) === $node->toString()) {
                $fullyQualifiedName = $this->getName($node);

                // the similar end is already imported → skip
                if ($this->shouldSkipName($fullyQualifiedName)) {
                    return null;
                }

                $shortName = $this->classNaming->getShortName($fullyQualifiedName);
                if (isset($this->newUseStatements[$shortName]) || isset($this->newFunctionUseStatements[$shortName])) {
                    if ($fullyQualifiedName === $this->newUseStatements[$shortName] || $fullyQualifiedName === $this->newFunctionUseStatements[$shortName]) {
                        return new Name($shortName);
                    }

                    return null;
                }

                if (! $this->importsInClassCollection->hasImport($fullyQualifiedName)) {
                    if ($node->getAttribute(AttributeKey::PARENT_NODE) instanceof FuncCall) {
                        $this->newFunctionUseStatements[$shortName] = $fullyQualifiedName;
                    } else {
                        $this->newUseStatements[$shortName] = $fullyQualifiedName;
                    }
                }

                // possibly aliased
                if (in_array($fullyQualifiedName, $this->aliasedUses, true)) {
                    return null;
                }

                $this->importsInClassCollection->addImport($fullyQualifiedName);

                return new Name($shortName);
            }

            return null;
        });

        if ($this->shouldImportDocBlocks) {
            // for doc blocks
            $this->callableNodeTraverser->traverseNodesWithCallable($node->stmts, function (Node $node): void {
                $importedDocUseStatements = $this->docBlockManipulator->importNames($node);
                $this->newUseStatements = array_merge($this->newUseStatements, $importedDocUseStatements);
            });
        }

        return $this->newUseStatements;
    }

    // 1. name is fully qualified → import it
    private function shouldSkipName(string $fullyQualifiedName): bool
    {
        // not namespaced class
        if (! Strings::contains($fullyQualifiedName, '\\')) {
            return true;
        }

        $shortName = $this->classNaming->getShortName($fullyQualifiedName);

        // nothing to change
        if ($shortName === $fullyQualifiedName) {
            return true;
        }

        return $this->importsInClassCollection->canImportBeAdded($fullyQualifiedName);
    }

    private function resolveAlreadyUsedShortNames(Namespace_ $namespace): void
    {
        if ($namespace->name instanceof Name) {
            $this->alreadyUsedShortNames[$namespace->name->toString()] = $namespace->name->toString();
        }

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $namespace->stmts, function (Node $node): void {
            if (! $node instanceof Name) {
                return;
            }

            $name = $node->getAttribute('originalName');
            if (! $name instanceof Name) {
                return;
            }

            // already short
            if (Strings::contains($name->toString(), '\\')) {
                return;
            }

            $this->alreadyUsedShortNames[$name->toString()] = $node->toString();
        });
    }

    private function isCurrentNamespace(string $namespaceName, string $newUseStatement): bool
    {
        $afterCurrentNamespace = Strings::after($newUseStatement, $namespaceName . '\\');
        if (! $afterCurrentNamespace) {
            return false;
        }

        return ! Strings::contains($afterCurrentNamespace, '\\');
    }

    private function resetCollectedNames(): void
    {
        $this->newUseStatements = [];
        $this->newFunctionUseStatements = [];
        $this->alreadyUsedShortNames = [];
        $this->importsInClassCollection->reset();
        $this->docBlockManipulator->resetImportedNames();
    }
}
