<?php

declare(strict_types=1);

namespace Rector\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;

final class VariableRenamer
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function renameVariableInClassMethodOrFunction(
        FunctionLike $functionLike,
        Assign $assign,
        string $oldName,
        string $expectedName
    ): void {
        $isRenamingActive = false;

        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $functionLike->stmts,
            function (Node $node) use ($oldName, $expectedName, $assign, &$isRenamingActive) {
                if ($node === $assign) {
                    $isRenamingActive = true;
                    return null;
                }

                if (! $isRenamingActive) {
                    return null;
                }

                if (! $node instanceof Variable) {
                    return null;
                }

                if (! $this->nodeNameResolver->isName($node, $oldName)) {
                    return null;
                }

                /** @var Variable $node */
                $node->name = new Identifier($expectedName);

                return $node;
            }
        );
    }
}
