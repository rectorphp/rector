<?php

declare (strict_types=1);
namespace Rector\Php56\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\AssignOp\Coalesce as AssignOpCoalesce;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast\Unset_ as UnsetCast;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class UndefinedVariableResolver
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\VariableAnalyzer
     */
    private $variableAnalyzer;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator, VariableAnalyzer $variableAnalyzer)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->variableAnalyzer = $variableAnalyzer;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    public function resolve($node) : array
    {
        $undefinedVariables = [];
        $checkedVariables = [];
        $currentStmt = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use(&$undefinedVariables, &$checkedVariables, &$currentStmt) : ?int {
            // entering new scope - break!
            if ($node instanceof FunctionLike && !$node instanceof ArrowFunction) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof Foreach_ || $node instanceof Case_) {
                // handled above
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof Stmt) {
                $currentStmt = $node;
                if ($currentStmt->getAttribute(AttributeKey::IS_UNREACHABLE) === \true) {
                    return NodeTraverser::STOP_TRAVERSAL;
                }
            }
            if (!$node instanceof Variable) {
                $checkedVariables = $this->resolveCheckedVariables($node, $checkedVariables);
                return null;
            }
            // after variable variable, the variable name got unpredictable, just stop
            if ($node->name instanceof Variable) {
                return NodeTraverser::STOP_TRAVERSAL;
            }
            $variableName = (string) $this->nodeNameResolver->getName($node);
            if ($this->shouldSkipVariable($node, $variableName, $checkedVariables, $currentStmt)) {
                return null;
            }
            /** @var string $variableName */
            $undefinedVariables[] = $variableName;
            return null;
        });
        return \array_unique($undefinedVariables);
    }
    /**
     * @param string[] $checkedVariables
     * @return string[]
     */
    private function resolveCheckedVariables(Node $node, array $checkedVariables) : array
    {
        if ($node instanceof Empty_ && $node->expr instanceof Variable) {
            $checkedVariables[] = (string) $this->nodeNameResolver->getName($node->expr);
            return $checkedVariables;
        }
        if ($node instanceof Isset_ || $node instanceof Unset_) {
            return $this->resolveCheckedVariablesFromIssetOrUnset($node, $checkedVariables);
        }
        if ($node instanceof UnsetCast && $node->expr instanceof Variable) {
            $checkedVariables[] = (string) $this->nodeNameResolver->getName($node->expr);
            return $checkedVariables;
        }
        if ($node instanceof Coalesce && $node->left instanceof Variable) {
            $checkedVariables[] = (string) $this->nodeNameResolver->getName($node->left);
            return $checkedVariables;
        }
        if ($node instanceof AssignOpCoalesce && $node->var instanceof Variable) {
            $checkedVariables[] = (string) $this->nodeNameResolver->getName($node->var);
            return $checkedVariables;
        }
        if ($node instanceof AssignRef && $node->var instanceof Variable) {
            $checkedVariables[] = (string) $this->nodeNameResolver->getName($node->var);
        }
        return $this->resolveCheckedVariablesFromArrayOrList($node, $checkedVariables);
    }
    /**
     * @param string[] $checkedVariables
     * @return string[]
     * @param \PhpParser\Node\Expr\Isset_|\PhpParser\Node\Stmt\Unset_ $node
     */
    private function resolveCheckedVariablesFromIssetOrUnset($node, array $checkedVariables) : array
    {
        foreach ($node->vars as $expr) {
            if ($expr instanceof Variable) {
                $checkedVariables[] = (string) $this->nodeNameResolver->getName($expr);
            }
        }
        return $checkedVariables;
    }
    /**
     * @param string[] $checkedVariables
     * @return string[]
     */
    private function resolveCheckedVariablesFromArrayOrList(Node $node, array $checkedVariables) : array
    {
        if (!$node instanceof Array_ && !$node instanceof List_) {
            return $checkedVariables;
        }
        foreach ($node->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (!$item->value instanceof Variable) {
                continue;
            }
            $checkedVariables[] = (string) $this->nodeNameResolver->getName($item->value);
        }
        return $checkedVariables;
    }
    private function hasVariableTypeOrCurrentStmtUnreachable(Variable $variable, ?string $variableName, ?Stmt $currentStmt) : bool
    {
        if (!\is_string($variableName)) {
            return \true;
        }
        // defined 100 %
        /** @var Scope $scope */
        $scope = $variable->getAttribute(AttributeKey::SCOPE);
        if ($scope->hasVariableType($variableName)->yes()) {
            return \true;
        }
        return $currentStmt instanceof Stmt && $currentStmt->getAttribute(AttributeKey::IS_UNREACHABLE) === \true;
    }
    /**
     * @param string[] $checkedVariables
     */
    private function shouldSkipVariable(Variable $variable, string $variableName, array &$checkedVariables, ?Stmt $currentStmt) : bool
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        // skip $this, as probably in outer scope
        if ($variableName === 'this') {
            return \true;
        }
        if ($variableName === null) {
            return \true;
        }
        if ($this->isDifferentWithOriginalNodeOrNoScope($variable)) {
            return \true;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($variable)) {
            return \true;
        }
        if (\in_array($variableName, $checkedVariables, \true)) {
            return \true;
        }
        if ($variable->getAttribute(AttributeKey::IS_BEING_ASSIGNED) === \true) {
            return \true;
        }
        return $this->hasVariableTypeOrCurrentStmtUnreachable($variable, $variableName, $currentStmt);
    }
    private function isDifferentWithOriginalNodeOrNoScope(Variable $variable) : bool
    {
        $originalNode = $variable->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$this->nodeComparator->areNodesEqual($variable, $originalNode)) {
            return \true;
        }
        $nodeScope = $variable->getAttribute(AttributeKey::SCOPE);
        return !$nodeScope instanceof Scope;
    }
}
