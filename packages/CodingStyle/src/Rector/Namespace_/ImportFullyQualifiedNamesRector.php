<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Exception\ShouldNotHappenException;
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

    public function __construct(CallableNodeTraverser $callableNodeTraverser)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
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
        return [Node\Stmt\Namespace_::class];
    }

    /**
     * @param Node\Stmt\Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->alreadyImportedUses = [];
        $this->newUseStatements = [];

        /** @var Node\Stmt\Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($node, Node\Stmt\Class_::class);
        if ($class === null) {
            return null;
        }

        $this->resolveAlreadyImporteUses($node);
        $newUseStatements = $this->importNamesAndCollectNewUseStatements($class);

        $this->addNewUseStatements($node, $newUseStatements);

        return $node;
    }

    private function resolveAlreadyImporteUses(Node\Stmt\Namespace_ $namespace): void
    {
        /** @var Node\Stmt\Use_[] $uses */
        $uses = $this->betterNodeFinder->findInstanceOf($namespace->stmts, Node\Stmt\Use_::class);

        foreach ($uses as $use) {
            // only import uses
            if ($use->type !== Node\Stmt\Use_::TYPE_NORMAL) {
                continue;
            }

            foreach ($use->uses as $useUse) {
                $name = $this->getName($useUse);
                if ($name === null) {
                    throw new ShouldNotHappenException();
                }

                if ($useUse->alias) {
                    // alias workaround
                    $this->aliasedUses[] = $name;
                }

                $this->alreadyImportedUses[] = $name;
            }
        }
    }

    /**
     * @param string[] $newUseStatements
     */
    private function addNewUseStatements(Node\Stmt\Namespace_ $namespace, array $newUseStatements): void
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

            $useUse = new Node\Stmt\UseUse(new Node\Name($newUseStatement));
            $newUses[] = new Node\Stmt\Use_([$useUse]);

            $this->alreadyImportedUses[] = $newUseStatement;
        }

        $namespace->stmts = array_merge($newUses, $namespace->stmts);
    }

    /**
     * @return string[]
     */
    private function importNamesAndCollectNewUseStatements(Node\Stmt\Class_ $class): array
    {
        $this->newUseStatements = [];

        $this->callableNodeTraverser->traverseNodesWithCallable([$class], function (Node $node) {
            if (! $node instanceof Node\Name) {
                return null;
            }

            // 1. name is fully qualified → import it
            if ($this->getName($node) === $node->toString()) {
                $fullyQualifiedName = $this->getName($node);
                if (! Strings::contains($fullyQualifiedName, '\\')) {
                    return null;
                }

                $shortName = Strings::after($fullyQualifiedName, '\\', -1);
                if ($shortName === false) {
                    throw new ShouldNotHappenException();
                }

                // nothing to change
                if ($shortName === $fullyQualifiedName) {
                    return null;
                }

                if (! in_array($fullyQualifiedName, $this->alreadyImportedUses, true)) {
                    $this->newUseStatements[] = $fullyQualifiedName;
                }

                // @todo detect aliases somehow, look like overrided by name resolver node traverser
                // possibly aliased
                if (in_array($fullyQualifiedName, $this->aliasedUses, true)) {
                    return null;
                }

                return new Node\Name($shortName);
            }
        });

        return $this->newUseStatements;
    }
}
