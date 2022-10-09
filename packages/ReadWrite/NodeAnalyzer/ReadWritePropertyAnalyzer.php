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
    public function __construct(AssignManipulator $assignManipulator, \Rector\ReadWrite\NodeAnalyzer\ReadExprAnalyzer $readExprAnalyzer, BetterNodeFinder $betterNodeFinder, PureFunctionDetector $pureFunctionDetector, array $parentNodeReadAnalyzers)
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
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            throw new ShouldNotHappenException();
        }
        foreach ($this->parentNodeReadAnalyzers as $parentNodeReadAnalyzer) {
            if ($parentNodeReadAnalyzer->isRead($node, $parentNode)) {
                return \true;
            }
        }
        if ($parentNode instanceof AssignOp) {
            return \true;
        }
        if (!$parentNode instanceof ArrayDimFetch) {
            return !$this->assignManipulator->isLeftPartOfAssign($node);
        }
        if ($parentNode->dim === $node && $this->isNotInsideIssetUnset($parentNode)) {
            return $this->isArrayDimFetchRead($parentNode);
        }
        if ($this->assignManipulator->isLeftPartOfAssign($parentNode)) {
            return \false;
        }
        if (!$this->isArrayDimFetchInImpureFunction($parentNode, $node)) {
            return $this->isNotInsideIssetUnset($parentNode);
        }
        return \false;
    }
    private function isArrayDimFetchInImpureFunction(ArrayDimFetch $arrayDimFetch, Node $node) : bool
    {
        if ($arrayDimFetch->var === $node) {
            $arg = $this->betterNodeFinder->findParentType($arrayDimFetch, Arg::class);
            if ($arg instanceof Arg) {
                $parentArg = $arg->getAttribute(AttributeKey::PARENT_NODE);
                if (!$parentArg instanceof FuncCall) {
                    return \false;
                }
                return !$this->pureFunctionDetector->detect($parentArg);
            }
        }
        return \false;
    }
    private function isNotInsideIssetUnset(ArrayDimFetch $arrayDimFetch) : bool
    {
        return !(bool) $this->betterNodeFinder->findParentByTypes($arrayDimFetch, [Isset_::class, Unset_::class]);
    }
    private function isArrayDimFetchRead(ArrayDimFetch $arrayDimFetch) : bool
    {
        $parentParentNode = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentParentNode instanceof Node) {
            throw new ShouldNotHappenException();
        }
        if (!$this->assignManipulator->isLeftPartOfAssign($arrayDimFetch)) {
            return \false;
        }
        if ($arrayDimFetch->var instanceof ArrayDimFetch) {
            return \true;
        }
        // the array dim fetch is assing here only; but the variable might be used later
        return $this->readExprAnalyzer->isExprRead($arrayDimFetch->var);
    }
}
