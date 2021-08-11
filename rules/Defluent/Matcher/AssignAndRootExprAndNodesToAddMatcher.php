<?php

declare (strict_types=1);
namespace Rector\Defluent\Matcher;

use PhpParser\Node\Expr\MethodCall;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor;
use Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory;
use Rector\Defluent\Skipper\FluentMethodCallSkipper;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class AssignAndRootExprAndNodesToAddMatcher
{
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor
     */
    private $fluentChainMethodCallRootExtractor;
    /**
     * @var \Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory
     */
    private $nonFluentChainMethodCallFactory;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer
     */
    private $sameClassMethodCallAnalyzer;
    /**
     * @var \Rector\Defluent\Skipper\FluentMethodCallSkipper
     */
    private $fluentMethodCallSkipper;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer, \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor $fluentChainMethodCallRootExtractor, \Rector\Defluent\NodeFactory\NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory, \Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer, \Rector\Defluent\Skipper\FluentMethodCallSkipper $fluentMethodCallSkipper)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->fluentChainMethodCallRootExtractor = $fluentChainMethodCallRootExtractor;
        $this->nonFluentChainMethodCallFactory = $nonFluentChainMethodCallFactory;
        $this->sameClassMethodCallAnalyzer = $sameClassMethodCallAnalyzer;
        $this->fluentMethodCallSkipper = $fluentMethodCallSkipper;
    }
    public function match(\PhpParser\Node\Expr\MethodCall $methodCall, string $kind) : ?\Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd
    {
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($methodCall);
        if (!$this->sameClassMethodCallAnalyzer->haveSingleClass($chainMethodCalls)) {
            return null;
        }
        $assignAndRootExpr = $this->fluentChainMethodCallRootExtractor->extractFromMethodCalls($chainMethodCalls, $kind);
        if (!$assignAndRootExpr instanceof \Rector\Defluent\ValueObject\AssignAndRootExpr) {
            return null;
        }
        if ($this->fluentMethodCallSkipper->shouldSkipMethodCalls($assignAndRootExpr, $chainMethodCalls)) {
            return null;
        }
        $parentMethodCall = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $nodesToAdd = $this->nonFluentChainMethodCallFactory->createFromAssignObjectAndMethodCalls($assignAndRootExpr, $chainMethodCalls, $kind, $parentMethodCall);
        return new \Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd($assignAndRootExpr, $nodesToAdd);
    }
}
