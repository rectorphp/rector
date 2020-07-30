<?php

declare(strict_types=1);

namespace Rector\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Naming\PhpDoc\VarTagValueNodeRenamer;
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

    /**
     * @var VarTagValueNodeRenamer
     */
    private $varTagValueNodeRenamer;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        VarTagValueNodeRenamer $varTagValueNodeRenamer
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->varTagValueNodeRenamer = $varTagValueNodeRenamer;
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function renameVariableInFunctionLike(
        FunctionLike $functionLike,
        ?Assign $assign = null,
        string $oldName,
        string $expectedName
    ): void {
        $isRenamingActive = false;

        if ($assign === null) {
            $isRenamingActive = true;
        }

        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $functionLike->stmts,
            function (Node $node) use ($oldName, $expectedName, $assign, &$isRenamingActive) {
                if ($assign !== null && $node === $assign) {
                    $isRenamingActive = true;
                    return null;
                }

                if ($this->isScopingNode($node)) {
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }

                if (! $node instanceof Variable) {
                    return null;
                }

                if (! $isRenamingActive) {
                    return null;
                }

                return $this->renameVariableIfMatchesName($node, $oldName, $expectedName);
            }
        );
    }

    private function renameVariableIfMatchesName(Variable $variable, string $oldName, string $expectedName): ?Variable
    {
        if (! $this->nodeNameResolver->isName($variable, $oldName)) {
            return null;
        }

        $variable->name = $expectedName;
        $this->varTagValueNodeRenamer->renameAssignVarTagVariableName($variable, $oldName, $expectedName);

        return $variable;
    }

    private function isScopingNode(Node $node): bool
    {
        return $node instanceof Closure || $node instanceof Function_ || $node instanceof ClassMethod;
    }
}
