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
use RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class UndefinedVariableResolver
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
    public function __construct(\RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\NodeAnalyzer\VariableAnalyzer $variableAnalyzer)
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
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $node->stmts, function (\PhpParser\Node $node) use(&$undefinedVariables) : ?int {
            // entering new scope - break!
            if ($node instanceof \PhpParser\Node\FunctionLike && !$node instanceof \PhpParser\Node\Expr\ArrowFunction) {
                return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
            }
            if ($node instanceof \PhpParser\Node\Stmt\Foreach_) {
                // handled above
                return \PhpParser\NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof \PhpParser\Node) {
                return null;
            }
            if ($this->shouldSkipVariable($node, $parentNode)) {
                return null;
            }
            $variableName = $this->nodeNameResolver->getName($node);
            if (!\is_string($variableName)) {
                return null;
            }
            // defined 100 %
            /** @var Scope $scope */
            $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            if ($scope->hasVariableType($variableName)->yes()) {
                return null;
            }
            $undefinedVariables[] = $variableName;
            return null;
        });
        return \array_unique($undefinedVariables);
    }
    private function issetOrUnsetOrEmptyParent(\PhpParser\Node $parentNode) : bool
    {
        return \in_array(\get_class($parentNode), [\PhpParser\Node\Stmt\Unset_::class, \PhpParser\Node\Expr\Cast\Unset_::class, \PhpParser\Node\Expr\Isset_::class, \PhpParser\Node\Expr\Empty_::class], \true);
    }
    private function isAsCoalesceLeftOrAssignOpCoalesceVar(\PhpParser\Node $parentNode, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        if ($parentNode instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce && $parentNode->left === $variable) {
            return \true;
        }
        if (!$parentNode instanceof \PhpParser\Node\Expr\AssignOp\Coalesce) {
            return \false;
        }
        return $parentNode->var === $variable;
    }
    private function isAssign(\PhpParser\Node $parentNode) : bool
    {
        return \in_array(\get_class($parentNode), [\PhpParser\Node\Expr\Assign::class, \PhpParser\Node\Expr\AssignRef::class], \true);
    }
    private function shouldSkipVariable(\PhpParser\Node\Expr\Variable $variable, \PhpParser\Node $parentNode) : bool
    {
        if ($this->variableAnalyzer->isStaticOrGlobal($variable)) {
            return \true;
        }
        if ($this->isAssign($parentNode)) {
            return \true;
        }
        if ($this->issetOrUnsetOrEmptyParent($parentNode)) {
            return \true;
        }
        if ($this->isAsCoalesceLeftOrAssignOpCoalesceVar($parentNode, $variable)) {
            return \true;
        }
        // list() = | [$values] = defines variables as null
        if ($this->isListAssign($parentNode)) {
            return \true;
        }
        if ($this->isDifferentWithOriginalNodeOrNoScope($variable)) {
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
        if ($this->hasPreviousCheckedWithIsset($variable)) {
            return \true;
        }
        if ($this->hasPreviousCheckedWithEmpty($variable)) {
            return \true;
        }
        return $this->isAfterSwitchCaseWithParentCase($variable);
    }
    private function isAfterSwitchCaseWithParentCase(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        $previousSwitch = $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (\PhpParser\Node $subNode) : bool {
            return $subNode instanceof \PhpParser\Node\Stmt\Switch_;
        });
        if (!$previousSwitch instanceof \PhpParser\Node\Stmt\Switch_) {
            return \false;
        }
        $parentSwitch = $previousSwitch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        return $parentSwitch instanceof \PhpParser\Node\Stmt\Case_;
    }
    private function isDifferentWithOriginalNodeOrNoScope(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        $originalNode = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
        if (!$this->nodeComparator->areNodesEqual($variable, $originalNode)) {
            return \true;
        }
        $nodeScope = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return !$nodeScope instanceof \PHPStan\Analyser\Scope;
    }
    private function hasPreviousCheckedWithIsset(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (\PhpParser\Node $subNode) use($variable) : bool {
            if (!$subNode instanceof \PhpParser\Node\Expr\Isset_) {
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
    private function hasPreviousCheckedWithEmpty(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (\PhpParser\Node $subNode) use($variable) : bool {
            if (!$subNode instanceof \PhpParser\Node\Expr\Empty_) {
                return \false;
            }
            $subNodeExpr = $subNode->expr;
            return $this->nodeComparator->areNodesEqual($subNodeExpr, $variable);
        });
    }
    private function isListAssign(\PhpParser\Node $node) : bool
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\List_) {
            return \true;
        }
        return $parentNode instanceof \PhpParser\Node\Expr\Array_;
    }
}
