<?php

declare (strict_types=1);
namespace Rector\ReadWrite\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Unset_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\DeadCode\SideEffect\PureFunctionDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
/**
 * Possibly re-use the same logic from PHPStan rule:
 * https://github.com/phpstan/phpstan-src/blob/8f16632f6ccb312159250bc06df5531fa4a1ff91/src/Rules/DeadCode/UnusedPrivatePropertyRule.php#L64-L116
 */
final class ReadWritePropertyAnalyzer
{
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
    /**
     * @var ParentNodeReadAnalyzerInterface[]
     * @readonly
     */
    private $parentNodeReadAnalyzers;
    /**
     * @param ParentNodeReadAnalyzerInterface[] $parentNodeReadAnalyzers
     */
    public function __construct(\Rector\Core\NodeManipulator\AssignManipulator $assignManipulator, \Rector\ReadWrite\NodeAnalyzer\ReadExprAnalyzer $readExprAnalyzer, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\DeadCode\SideEffect\PureFunctionDetector $pureFunctionDetector, array $parentNodeReadAnalyzers)
    {
        $this->assignManipulator = $assignManipulator;
        $this->readExprAnalyzer = $readExprAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->pureFunctionDetector = $pureFunctionDetector;
        $this->parentNodeReadAnalyzers = $parentNodeReadAnalyzers;
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
        foreach ($this->parentNodeReadAnalyzers as $parentNodeReadAnalyzer) {
            if ($parentNodeReadAnalyzer->isRead($node, $parent)) {
                return \true;
            }
        }
        if ($parent instanceof \PhpParser\Node\Expr\AssignOp) {
            return \true;
        }
        if ($parent instanceof \PhpParser\Node\Expr\ArrayDimFetch && $parent->dim === $node && $this->isNotInsideIssetUnset($parent)) {
            return $this->isArrayDimFetchRead($parent);
        }
        if (!$parent instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return !$this->assignManipulator->isLeftPartOfAssign($node);
        }
        if ($this->assignManipulator->isLeftPartOfAssign($parent)) {
            return \false;
        }
        return !$this->isArrayDimFetchInImpureFunction($parent, $node);
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
