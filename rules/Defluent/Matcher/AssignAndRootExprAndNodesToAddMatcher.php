<?php

declare(strict_types=1);

namespace Rector\Defluent\Matcher;

use PhpParser\Node\Expr\MethodCall;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor;
use Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory;
use Rector\Defluent\Skipper\FluentMethodCallSkipper;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd;

final class AssignAndRootExprAndNodesToAddMatcher
{
    public function __construct(
        private FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer,
        private FluentChainMethodCallRootExtractor $fluentChainMethodCallRootExtractor,
        private NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory,
        private SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer,
        private FluentMethodCallSkipper $fluentMethodCallSkipper
    ) {
    }

    public function match(MethodCall $methodCall, string $kind): ?AssignAndRootExprAndNodesToAdd
    {
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
}
