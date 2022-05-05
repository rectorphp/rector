<?php

declare(strict_types=1);

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
     * @param ParentNodeReadAnalyzerInterface[] $parentNodeReadAnalyzers
     */
    public function __construct(
        private readonly AssignManipulator $assignManipulator,
        private readonly ReadExprAnalyzer $readExprAnalyzer,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly PureFunctionDetector $pureFunctionDetector,
        private readonly array $parentNodeReadAnalyzers
    ) {
    }

    public function isRead(PropertyFetch | StaticPropertyFetch $node): bool
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            throw new ShouldNotHappenException();
        }

        foreach ($this->parentNodeReadAnalyzers as $parentNodeReadAnalyzer) {
            if ($parentNodeReadAnalyzer->isRead($node, $parent)) {
                return true;
            }
        }

        if ($parent instanceof AssignOp) {
            return true;
        }

        if ($parent instanceof ArrayDimFetch && $parent->dim === $node && $this->isNotInsideIssetUnset($parent)) {
            return $this->isArrayDimFetchRead($parent);
        }

        if (! $parent instanceof ArrayDimFetch) {
            return ! $this->assignManipulator->isLeftPartOfAssign($node);
        }

        if (! $this->isArrayDimFetchInImpureFunction($parent, $node)) {
            return ! $this->assignManipulator->isLeftPartOfAssign($node);
        }

        return false;
    }

    private function isArrayDimFetchInImpureFunction(ArrayDimFetch $arrayDimFetch, Node $node): bool
    {
        if ($arrayDimFetch->var === $node) {
            $arg = $this->betterNodeFinder->findParentType($arrayDimFetch, Arg::class);
            if ($arg instanceof Arg) {
                $parentArg = $arg->getAttribute(AttributeKey::PARENT_NODE);
                if (! $parentArg instanceof FuncCall) {
                    return false;
                }

                return ! $this->pureFunctionDetector->detect($parentArg);
            }
        }

        return false;
    }

    private function isNotInsideIssetUnset(ArrayDimFetch $arrayDimFetch): bool
    {
        return ! (bool) $this->betterNodeFinder->findParentByTypes($arrayDimFetch, [Isset_::class, Unset_::class]);
    }

    private function isArrayDimFetchRead(ArrayDimFetch $arrayDimFetch): bool
    {
        $parentParent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof Node) {
            throw new ShouldNotHappenException();
        }

        if (! $this->assignManipulator->isLeftPartOfAssign($arrayDimFetch)) {
            return false;
        }

        if ($arrayDimFetch->var instanceof ArrayDimFetch) {
            return true;
        }

        // the array dim fetch is assing here only; but the variable might be used later
        return $this->readExprAnalyzer->isExprRead($arrayDimFetch->var);
    }
}
