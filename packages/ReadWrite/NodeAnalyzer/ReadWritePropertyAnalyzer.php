<?php

declare (strict_types=1);
namespace Rector\ReadWrite\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Unset_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\DeadCode\SideEffect\PureFunctionDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
final class ReadWritePropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\ReadWrite\Guard\VariableToConstantGuard
     */
    private $variableToConstantGuard;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    /**
     * @readonly
     * @var \Rector\ReadWrite\NodeAnalyzer\ReadExprAnalyzer
     */
    private $readExprAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\PureFunctionDetector
     */
    private $pureFunctionDetector;
    public function __construct(\Rector\ReadWrite\Guard\VariableToConstantGuard $variableToConstantGuard, \Rector\Core\NodeManipulator\AssignManipulator $assignManipulator, \Rector\ReadWrite\NodeAnalyzer\ReadExprAnalyzer $readExprAnalyzer, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\DeadCode\SideEffect\PureFunctionDetector $pureFunctionDetector)
    {
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->assignManipulator = $assignManipulator;
        $this->readExprAnalyzer = $readExprAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->pureFunctionDetector = $pureFunctionDetector;
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $node
     */
    public function isRead($node) : bool
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $parent = $this->unwrapPostPreIncDec($parent);
        if ($parent instanceof \PhpParser\Node\Arg) {
            $readArg = $this->variableToConstantGuard->isReadArg($parent);
            if ($readArg) {
                return \true;
            }
        }
        if ($parent instanceof \PhpParser\Node\Expr\ArrayDimFetch && $parent->dim === $node && $this->isNotInsideIssetUnset($parent)) {
            return $this->isArrayDimFetchRead($parent);
        }
        if (!$parent instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return !$this->assignManipulator->isLeftPartOfAssign($node);
        }
        if (!$this->isArrayDimFetchInImpureFunction($parent, $node)) {
            return !$this->assignManipulator->isLeftPartOfAssign($node);
        }
        return \false;
    }
    private function isArrayDimFetchInImpureFunction(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, \PhpParser\Node $node) : bool
    {
        if ($arrayDimFetch->var === $node) {
            $arg = $this->betterNodeFinder->findParentType($arrayDimFetch, \PhpParser\Node\Arg::class);
            if ($arg instanceof \PhpParser\Node\Arg) {
                $parentArg = $arg->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
                if (!$parentArg instanceof \PhpParser\Node\Expr\FuncCall) {
                    return \false;
                }
                return !$this->pureFunctionDetector->detect($parentArg);
            }
        }
        return \false;
    }
    private function unwrapPostPreIncDec(\PhpParser\Node $node) : \PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\PreInc || $node instanceof \PhpParser\Node\Expr\PreDec || $node instanceof \PhpParser\Node\Expr\PostInc || $node instanceof \PhpParser\Node\Expr\PostDec) {
            $node = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$node instanceof \PhpParser\Node) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
        }
        return $node;
    }
    private function isNotInsideIssetUnset(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        return !(bool) $this->betterNodeFinder->findParentByTypes($arrayDimFetch, [\PhpParser\Node\Expr\Isset_::class, \PhpParser\Node\Stmt\Unset_::class]);
    }
    private function isArrayDimFetchRead(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        $parentParent = $arrayDimFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentParent instanceof \PhpParser\Node) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if (!$this->assignManipulator->isLeftPartOfAssign($arrayDimFetch)) {
            return \false;
        }
        if ($arrayDimFetch->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \true;
        }
        // the array dim fetch is assing here only; but the variable might be used later
        return $this->readExprAnalyzer->isExprRead($arrayDimFetch->var);
    }
}
