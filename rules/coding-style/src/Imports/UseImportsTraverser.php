<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Imports;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;

final class UseImportsTraverser
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @param Stmt[] $stmts
     */
    public function traverserStmtsForFunctions(array $stmts, callable $callable): void
    {
        $this->traverseForType($stmts, $callable, Use_::TYPE_FUNCTION);
    }

    /**
     * @param Stmt[] $stmts
     */
    public function traverserStmts(array $stmts, callable $callable): void
    {
        $this->traverseForType($stmts, $callable, Use_::TYPE_NORMAL);
    }

    /**
     * @param Stmt[] $stmts
     */
    private function traverseForType(array $stmts, callable $callable, int $desiredType): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use (
            $callable,
            $desiredType
        ) {
            if ($node instanceof Use_) {
                // only import uses
                if ($node->type !== $desiredType) {
                    return null;
                }

                foreach ($node->uses as $useUse) {
                    $name = $this->nodeNameResolver->getName($useUse);
                    $callable($useUse, $name);
                }
            }

            if ($node instanceof GroupUse) {
                $this->processGroupUse($node, $desiredType, $callable);
            }

            return null;
        });
    }

    private function processGroupUse(GroupUse $groupUse, int $desiredType, callable $callable): void
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
