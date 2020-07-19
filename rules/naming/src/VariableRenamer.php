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

    private function renameVariableIfMatchesName(Variable $variable, string $oldName, string $expectedName)
    {
        if (! $this->nodeNameResolver->isName($variable, $oldName)) {
            return null;
        }

        $variable->name = $expectedName;
        $this->varTagValueNodeRenamer->renameAssignVarTagVariableName($variable, $oldName, $expectedName);

        return $variable;
    }
}
