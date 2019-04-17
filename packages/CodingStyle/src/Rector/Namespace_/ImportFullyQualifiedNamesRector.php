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
        return [Namespace_::class];
    }

    /**
     * @param Node\Stmt\Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->alreadyImportedUses = [];
        $this->newUseStatements = [];

        /** @var Node\Stmt\Class_|null $class */
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
        /** @var Node\Stmt\Use_[] $uses */
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
        $this->newUseStatements = [];

        $this->callableNodeTraverser->traverseNodesWithCallable([$class], function (Node $node) {
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

            // 1. name is fully qualified → import it
            if ($this->getName($node) === $node->toString()) {
                $fullyQualifiedName = $this->getName($node);
                if (! Strings::contains($fullyQualifiedName, '\\')) {
                    return null;
                }

                $shortName = $this->getShortName($fullyQualifiedName);

                // nothing to change
                if ($shortName === $fullyQualifiedName) {
                    return null;
                }

                // the similar end is already imported → skip
                foreach ($this->alreadyImportedUses as $alreadyImportedUse) {
                    if ($this->getShortName(
                        $alreadyImportedUse
                    ) === $shortName && $alreadyImportedUse !== $fullyQualifiedName) {
                        return null;
                    }
                }

                if (! in_array($fullyQualifiedName, $this->alreadyImportedUses, true)) {
                    $this->newUseStatements[] = $fullyQualifiedName;
                }

                // @todo detect aliases somehow, look like overrided by name resolver node traverser
                // possibly aliased
                if (in_array($fullyQualifiedName, $this->aliasedUses, true)) {
                    return null;
                }

                return new Name($shortName);
            }
        });

        return $this->newUseStatements;
    }

    private function getShortName(string $fullyQualifiedName): string
    {
        if (! Strings::contains($fullyQualifiedName, '\\')) {
            return $fullyQualifiedName;
        }

        return Strings::after($fullyQualifiedName, '\\', -1);
    }
}
