<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor;
use Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory;
use Rector\Defluent\Skipper\FluentMethodCallSkipper;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractFluentChainMethodCallRector extends AbstractRector
{
    /**
     * @var FluentChainMethodCallNodeAnalyzer
     */
    protected $fluentChainMethodCallNodeAnalyzer;

    /**
     * @var NonFluentChainMethodCallFactory
     */
    protected $nonFluentChainMethodCallFactory;

    /**
     * @var FluentChainMethodCallRootExtractor
     */
    protected $fluentChainMethodCallRootExtractor;

    /**
     * @var SameClassMethodCallAnalyzer
     */
    protected $sameClassMethodCallAnalyzer;

    /**
     * @var FluentMethodCallSkipper
     */
    protected $fluentMethodCallSkipper;

    /**
     * @required
     */
    public function autowireAbstractFluentChainMethodCallRector(
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer,
        FluentChainMethodCallRootExtractor $fluentChainMethodCallRootExtractor,
        NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory,
        SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer,
        FluentMethodCallSkipper $fluentMethodCallSkipper
    ): void {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->fluentChainMethodCallRootExtractor = $fluentChainMethodCallRootExtractor;
        $this->nonFluentChainMethodCallFactory = $nonFluentChainMethodCallFactory;
        $this->sameClassMethodCallAnalyzer = $sameClassMethodCallAnalyzer;
        $this->fluentMethodCallSkipper = $fluentMethodCallSkipper;
    }

    protected function createStandaloneNodesToAddFromChainMethodCalls(
        MethodCall $methodCall,
        string $kind
    ): ?AssignAndRootExprAndNodesToAdd {
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($methodCall);
        if (! $this->sameClassMethodCallAnalyzer->haveSingleClass($chainMethodCalls)) {
            return null;
        }

        $assignAndRootExpr = $this->fluentChainMethodCallRootExtractor->extractFromMethodCalls(
            $chainMethodCalls,
            $kind
        );

        if (! $assignAndRootExpr instanceof AssignAndRootExpr) {
            return null;
        }

        if ($this->fluentMethodCallSkipper->shouldSkipMethodCalls($assignAndRootExpr, $chainMethodCalls)) {
            return null;
        }

        $nodesToAdd = $this->nonFluentChainMethodCallFactory->createFromAssignObjectAndMethodCalls(
            $assignAndRootExpr,
            $chainMethodCalls,
            $kind
        );

        return new AssignAndRootExprAndNodesToAdd($assignAndRootExpr, $nodesToAdd);
    }

    /**
     * @param MethodCall|Return_ $node
     */
    protected function removeCurrentNode(Node $node): void
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Assign) {
            $this->removeNode($parent);
            return;
        }

        // part of method call
        if ($parent instanceof Arg) {
            $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParent instanceof MethodCall) {
                $this->removeNode($parentParent);
            }

            return;
        }

        $this->removeNode($node);
    }

    protected function shouldSkipMethodCall(MethodCall $methodCall): bool
    {
        return $this->fluentMethodCallSkipper->shouldSkipRootMethodCall($methodCall);
    }

    protected function matchReturnMethodCall(Return_ $return): ?MethodCall
    {
        if ($return->expr === null) {
            return null;
        }

        if (! $return->expr instanceof MethodCall) {
            return null;
        }

        return $return->expr;
    }

    protected function shouldSkipMethodCallIncludingNew(MethodCall $methodCall): bool
    {
        if ($this->shouldSkipMethodCall($methodCall)) {
            return true;
        }

        $chainRootExpr = $this->fluentChainMethodCallNodeAnalyzer->resolveRootExpr($methodCall);
        return $chainRootExpr instanceof New_;
    }
}
