<?php

declare (strict_types=1);
namespace Rector\Php56\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
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
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\VariableAnalyzer
     */
    private $variableAnalyzer;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator, BetterNodeFinder $betterNodeFinder, VariableAnalyzer $variableAnalyzer)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->variableAnalyzer = $variableAnalyzer;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    public function resolve($node) : array
    {
        $undefinedVariables = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use(&$undefinedVariables) : ?int {
            // entering new scope - break!
            if ($node instanceof FunctionLike && !$node instanceof ArrowFunction) {
                return NodeTraverser::STOP_TRAVERSAL;
            }
            if ($node instanceof Foreach_) {
                // handled above
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            /**
             * The Node that doesn't have origNode attribute yet
             * means the Node is a replacement below other changed node
             */
            if (!$node->hasAttribute(AttributeKey::ORIGINAL_NODE)) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Variable) {
                return null;
            }
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof Node) {
                return null;
            }
            if ($this->shouldSkipVariable($node, $parentNode)) {
                return null;
            }
            $variableName = $this->nodeNameResolver->getName($node);
            if ($this->hasVariableTypeOrCurrentStmtUnreachable($node, $variableName)) {
                return null;
            }
            /** @var string $variableName */
            $undefinedVariables[] = $variableName;
            return null;
        });
        return \array_unique($undefinedVariables);
    }
    private function hasVariableTypeOrCurrentStmtUnreachable(Variable $variable, ?string $variableName) : bool
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
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($variable);
        return $currentStmt instanceof Stmt && $currentStmt->getAttribute(AttributeKey::IS_UNREACHABLE) === \true;
    }
    private function issetOrUnsetOrEmptyParent(Node $parentNode) : bool
    {
        return \in_array(\get_class($parentNode), [Unset_::class, UnsetCast::class, Isset_::class, Empty_::class], \true);
    }
    private function isAsCoalesceLeftOrAssignOpCoalesceVar(Node $parentNode, Variable $variable) : bool
    {
        if ($parentNode instanceof Coalesce && $parentNode->left === $variable) {
            return \true;
        }
        if (!$parentNode instanceof AssignOpCoalesce) {
            return \false;
        }
        return $parentNode->var === $variable;
    }
    private function isAssign(Node $parentNode) : bool
    {
        return \in_array(\get_class($parentNode), [Assign::class, AssignRef::class], \true);
    }
    private function shouldSkipVariable(Variable $variable, Node $parentNode) : bool
    {
        if ($this->isAsCoalesceLeftOrAssignOpCoalesceVar($parentNode, $variable)) {
            return \true;
        }
        if ($this->isAssign($parentNode)) {
            return \true;
        }
        if ($this->issetOrUnsetOrEmptyParent($parentNode)) {
            return \true;
        }
        // list() = | [$values] = defines variables as null
        if ($this->isListAssign($parentNode)) {
            return \true;
        }
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
        if ($this->hasPreviousCheckedWithIsset($variable)) {
            return \true;
        }
        if ($this->hasPreviousCheckedWithEmpty($variable)) {
            return \true;
        }
        return $this->isAfterSwitchCaseWithParentCase($variable);
    }
    private function isAfterSwitchCaseWithParentCase(Variable $variable) : bool
    {
        $previousSwitch = $this->betterNodeFinder->findFirstPrevious($variable, static function (Node $subNode) : bool {
            return $subNode instanceof Switch_;
        });
        if (!$previousSwitch instanceof Switch_) {
            return \false;
        }
        $parentNode = $previousSwitch->getAttribute(AttributeKey::PARENT_NODE);
        return $parentNode instanceof Case_;
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
    private function hasPreviousCheckedWithIsset(Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($variable, function (Node $subNode) use($variable) : bool {
            if (!$subNode instanceof Isset_) {
                return \false;
            }
            $vars = $subNode->vars;
            foreach ($vars as $var) {
                if ($this->nodeComparator->areNodesEqual($variable, $var)) {
                    return \true;
                }
            }
            return \false;
        });
    }
    private function hasPreviousCheckedWithEmpty(Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($variable, function (Node $subNode) use($variable) : bool {
            if (!$subNode instanceof Empty_) {
                return \false;
            }
            $subNodeExpr = $subNode->expr;
            return $this->nodeComparator->areNodesEqual($subNodeExpr, $variable);
        });
    }
    private function isListAssign(Node $node) : bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof List_) {
            return \true;
        }
        return $parentNode instanceof Array_;
    }
}
