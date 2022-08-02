<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class UseImportsTraverser
{
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Stmt[] $stmts
     * @param callable(UseUse $useUse, string $name): void $callable
     */
    public function traverserStmts(array $stmts, callable $callable) : void
    {
        $this->traverseForType($stmts, $callable, Use_::TYPE_NORMAL);
    }
    /**
     * @param Stmt[] $stmts
     * @param callable(UseUse $useUse, string $name): void $callable
     */
    public function traverserStmtsForFunctions(array $stmts, callable $callable) : void
    {
        $this->traverseForType($stmts, $callable, Use_::TYPE_FUNCTION);
    }
    /**
     * @param callable(UseUse $useUse, string $name): void $callable
     * @param Stmt[] $stmts
     */
    private function traverseForType(array $stmts, callable $callable, int $desiredType) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use($callable, $desiredType) {
            if ($node instanceof Use_) {
                // only import uses
                if ($node->type !== $desiredType) {
                    return null;
                }
                foreach ($node->uses as $useUse) {
                    $name = $this->nodeNameResolver->getName($useUse);
                    if ($name === null) {
                        continue;
                    }
                    $callable($useUse, $name);
                }
            }
            if ($node instanceof GroupUse) {
                $this->processGroupUse($node, $desiredType, $callable);
            }
            return null;
        });
    }
    /**
     * @param callable(UseUse $useUse, string $name): void $callable
     */
    private function processGroupUse(GroupUse $groupUse, int $desiredType, callable $callable) : void
    {
        if ($groupUse->type !== Use_::TYPE_UNKNOWN) {
            return;
        }
        $prefixName = $groupUse->prefix->toString();
        foreach ($groupUse->uses as $useUse) {
            if ($useUse->type !== $desiredType) {
                continue;
            }
            $name = $prefixName . '\\' . $this->nodeNameResolver->getName($useUse);
            $callable($useUse, $name);
        }
    }
}
