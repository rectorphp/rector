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
use Rector\PhpParser\NodeTraverser\SimpleNodeTraverser;
/**
 * Marks by-ref returns IS_INSIDE_BYREF_FUNCTION_LIKE and by-ref variables
 * (IS_BYREF_VAR) from a single FunctionLike entry, sharing the one node subscription.
 */
final class ByRefNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    /**
     * By-ref variable names per entered function-like, tracked during the main traversal,
     * instead of traversing the function-like stmts again on every enter
     *
     * @var array<string[]>
     */
    private array $byRefVariableNamesStack = [];
    /**
     * @param Node[] $nodes
     * @return null
     */
    public function beforeTraverse(array $nodes)
    {
        $this->byRefVariableNamesStack = [];
        return null;
    }
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof AssignRef) {
            $node->expr->setAttribute(AttributeKey::IS_BYREF_VAR, \true);
            return null;
        }
        if ($node instanceof Variable) {
            $this->decorateByRefVariable($node);
            return null;
        }
        if (!$node instanceof FunctionLike) {
            return null;
        }
        $stmts = $node->getStmts();
        if ($stmts === null) {
            // keep the stack in sync with leaveNode()
            $this->byRefVariableNamesStack[] = [];
            return null;
        }
        $this->decorateByRefReturn($node, $stmts);
        // closure by-ref use is a by-ref variable in the outer function-likes as well
        $closureUseVariableNames = $this->resolveClosureUseIsByRefAttribute($node);
        if ($closureUseVariableNames !== []) {
            foreach ($this->byRefVariableNamesStack as $key => $byRefVariableNames) {
                $this->byRefVariableNamesStack[$key] = array_merge($byRefVariableNames, $closureUseVariableNames);
            }
        }
        $this->byRefVariableNamesStack[] = array_merge($closureUseVariableNames, $this->resolveParamIsByRefAttribute($node));
        return null;
    }
    /**
     * @return null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof FunctionLike) {
            array_pop($this->byRefVariableNamesStack);
        }
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
    private function decorateByRefVariable(Variable $variable): void
    {
        if (!is_string($variable->name)) {
            return;
        }
        foreach ($this->byRefVariableNamesStack as $byRefVariableNames) {
            if (in_array($variable->name, $byRefVariableNames, \true)) {
                $variable->setAttribute(AttributeKey::IS_BYREF_VAR, \true);
                return;
            }
        }
    }
    /**
     * @return string[]
     */
    private function resolveParamIsByRefAttribute(FunctionLike $functionLike): array
    {
        $byRefVariableNames = [];
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
     * @return string[]
     */
    private function resolveClosureUseIsByRefAttribute(FunctionLike $functionLike): array
    {
        if (!$functionLike instanceof Closure) {
            return [];
        }
        $byRefVariableNames = [];
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
