<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\NodeTraverser\SimpleNodeTraverser;
/**
 * Marks by-ref returns IS_INSIDE_BYREF_FUNCTION_LIKE and by-ref variables
 * (IS_BYREF_VAR) from a single FunctionLike entry, sharing the one node subscription.
 */
final class ByRefNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof AssignRef) {
            $node->expr->setAttribute(AttributeKey::IS_BYREF_VAR, \true);
            return null;
        }
        if (!$node instanceof FunctionLike) {
            return null;
        }
        $stmts = $node->getStmts();
        if ($stmts === null) {
            return null;
        }
        $this->decorateByRefReturn($node, $stmts);
        $this->decorateByRefVariables($node, $stmts);
        return null;
    }
    /**
     * @param Node\Stmt[] $stmts
     */
    private function decorateByRefReturn(FunctionLike $functionLike, array $stmts): void
    {
        if (!$functionLike->returnsByRef()) {
            return;
        }
        SimpleNodeTraverser::decorateWithAttributeValue($stmts, AttributeKey::IS_INSIDE_BYREF_FUNCTION_LIKE, \true);
    }
    /**
     * @param Node\Stmt[] $stmts
     */
    private function decorateByRefVariables(FunctionLike $functionLike, array $stmts): void
    {
        $byRefVariableNames = $this->resolveClosureUseIsByRefAttribute($functionLike, []);
        $byRefVariableNames = $this->resolveParamIsByRefAttribute($functionLike, $byRefVariableNames);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $subNode) use (&$byRefVariableNames): ?\PhpParser\Node\Expr\Variable {
            if ($subNode instanceof Closure) {
                $byRefVariableNames = $this->resolveClosureUseIsByRefAttribute($subNode, $byRefVariableNames);
                return null;
            }
            if (!$subNode instanceof Variable) {
                return null;
            }
            if (!in_array($subNode->name, $byRefVariableNames, \true)) {
                return null;
            }
            $subNode->setAttribute(AttributeKey::IS_BYREF_VAR, \true);
            return $subNode;
        });
    }
    /**
     * @param string[] $byRefVariableNames
     * @return string[]
     */
    private function resolveParamIsByRefAttribute(FunctionLike $functionLike, array $byRefVariableNames): array
    {
        foreach ($functionLike->getParams() as $param) {
            if ($param->byRef && $param->var instanceof Variable && !$param->var->name instanceof Expr) {
                $param->var->setAttribute(AttributeKey::IS_BYREF_VAR, \true);
                /** @var string $paramVarName */
                $paramVarName = $param->var->name;
                $byRefVariableNames[] = $paramVarName;
            }
        }
        return $byRefVariableNames;
    }
    /**
     * @param string[] $byRefVariableNames
     * @return string[]
     */
    private function resolveClosureUseIsByRefAttribute(FunctionLike $functionLike, array $byRefVariableNames): array
    {
        if (!$functionLike instanceof Closure) {
            return $byRefVariableNames;
        }
        foreach ($functionLike->uses as $closureUse) {
            if ($closureUse->byRef && !$closureUse->var->name instanceof Expr) {
                $closureUse->var->setAttribute(AttributeKey::IS_BYREF_VAR, \true);
                /** @var string $closureVarName */
                $closureVarName = $closureUse->var->name;
                $byRefVariableNames[] = $closureVarName;
            }
        }
        return $byRefVariableNames;
    }
}
