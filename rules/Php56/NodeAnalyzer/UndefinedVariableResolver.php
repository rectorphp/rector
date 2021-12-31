<?php

declare (strict_types=1);
namespace Rector\Php56\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast\Unset_ as UnsetCast;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20211231\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
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
    public function __construct(\RectorPrefix20211231\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    public function resolve($node) : array
    {
        $undefinedVariables = [];
        $variableNamesFromParams = $this->collectVariableNamesFromParams($node);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $node->stmts, function (\PhpParser\Node $node) use(&$undefinedVariables, $variableNamesFromParams) : ?int {
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
            if ($this->shouldSkipVariable($node)) {
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
            if (\in_array($variableName, $variableNamesFromParams, \true)) {
                return null;
            }
            $undefinedVariables[] = $variableName;
            return null;
        });
        return \array_unique($undefinedVariables);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function collectVariableNamesFromParams($node) : array
    {
        $variableNames = [];
        foreach ($node->getParams() as $param) {
            if ($param->var instanceof \PhpParser\Node\Expr\Variable) {
                $variableNames[] = (string) $this->nodeNameResolver->getName($param->var);
            }
        }
        return $variableNames;
    }
    private function issetOrUnsetParent(\PhpParser\Node $parentNode) : bool
    {
        return \in_array(\get_class($parentNode), [\PhpParser\Node\Stmt\Unset_::class, \PhpParser\Node\Expr\Cast\Unset_::class, \PhpParser\Node\Expr\Isset_::class], \true);
    }
    private function isAsCoalesceLeft(\PhpParser\Node $parentNode, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        return $parentNode instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce && $parentNode->left === $variable;
    }
    private function isAssignOrStaticVariableParent(\PhpParser\Node $parentNode) : bool
    {
        if (\in_array(\get_class($parentNode), [\PhpParser\Node\Expr\Assign::class, \PhpParser\Node\Expr\AssignRef::class], \true)) {
            return \true;
        }
        return $this->isStaticVariable($parentNode);
    }
    private function shouldSkipVariable(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        $parentNode = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node\Stmt\Global_) {
            return \true;
        }
        if ($this->isAssignOrStaticVariableParent($parentNode)) {
            return \true;
        }
        if ($this->issetOrUnsetParent($parentNode)) {
            return \true;
        }
        if ($this->isAsCoalesceLeft($parentNode, $variable)) {
            return \true;
        }
        // list() = | [$values] = defines variables as null
        if ($this->isListAssign($parentNode)) {
            return \true;
        }
        $nodeScope = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$nodeScope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        $originalNode = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
        if (!$this->nodeComparator->areNodesEqual($variable, $originalNode)) {
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
        return $this->hasPreviousCheckedWithIsset($variable);
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
    private function isStaticVariable(\PhpParser\Node $parentNode) : bool
    {
        // definition of static variable
        if ($parentNode instanceof \PhpParser\Node\Stmt\StaticVar) {
            $parentParentNode = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof \PhpParser\Node\Stmt\Static_) {
                return \true;
            }
        }
        return \false;
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
