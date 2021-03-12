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
    /**
     * @var FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;

    /**
     * @var NonFluentChainMethodCallFactory
     */
    private $nonFluentChainMethodCallFactory;

    /**
     * @var FluentMethodCallSkipper
     */
    private $fluentMethodCallSkipper;

    /**
     * @var FluentChainMethodCallRootExtractor
     */
    private $fluentChainMethodCallRootExtractor;

    /**
     * @var SameClassMethodCallAnalyzer
     */
    private $sameClassMethodCallAnalyzer;

    public function __construct(
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer,
        FluentChainMethodCallRootExtractor $fluentChainMethodCallRootExtractor,
        NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory,
        SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer,
        FluentMethodCallSkipper $fluentMethodCallSkipper
    ) {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->fluentChainMethodCallRootExtractor = $fluentChainMethodCallRootExtractor;
        $this->nonFluentChainMethodCallFactory = $nonFluentChainMethodCallFactory;
        $this->sameClassMethodCallAnalyzer = $sameClassMethodCallAnalyzer;
        $this->fluentMethodCallSkipper = $fluentMethodCallSkipper;
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
