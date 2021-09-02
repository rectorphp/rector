<?php

declare (strict_types=1);
namespace Rector\Defluent\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\MethodCallSkipAnalyzer;
use Rector\Defluent\NodeFactory\ReturnFluentMethodCallFactory;
use Rector\Defluent\NodeFactory\SeparateReturnMethodCallFactory;
use Rector\Defluent\Skipper\FluentMethodCallSkipper;
use Rector\Defluent\ValueObject\FirstAssignFluentCall;
use Rector\Defluent\ValueObject\FluentMethodCalls;
use Rector\Defluent\ValueObjectFactory\FluentMethodCallsFactory;
use Rector\Symfony\NodeAnalyzer\FluentNodeRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 *
 * @see \Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\ReturnFluentChainMethodCallToNormalMethodCallRectorTest
 */
final class ReturnFluentChainMethodCallToNormalMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Defluent\NodeFactory\ReturnFluentMethodCallFactory
     */
    private $returnFluentMethodCallFactory;
    /**
     * @var \Rector\Defluent\ValueObjectFactory\FluentMethodCallsFactory
     */
    private $fluentMethodCallsFactory;
    /**
     * @var \Rector\Defluent\NodeFactory\SeparateReturnMethodCallFactory
     */
    private $separateReturnMethodCallFactory;
    /**
     * @var \Rector\Symfony\NodeAnalyzer\FluentNodeRemover
     */
    private $fluentNodeRemover;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\MethodCallSkipAnalyzer
     */
    private $methodCallSkipAnalyzer;
    /**
     * @var \Rector\Defluent\Skipper\FluentMethodCallSkipper
     */
    private $fluentMethodCallSkipper;
    public function __construct(\Rector\Defluent\NodeFactory\ReturnFluentMethodCallFactory $returnFluentMethodCallFactory, \Rector\Defluent\ValueObjectFactory\FluentMethodCallsFactory $fluentMethodCallsFactory, \Rector\Defluent\NodeFactory\SeparateReturnMethodCallFactory $separateReturnMethodCallFactory, \Rector\Symfony\NodeAnalyzer\FluentNodeRemover $fluentNodeRemover, \Rector\Defluent\NodeAnalyzer\MethodCallSkipAnalyzer $methodCallSkipAnalyzer, \Rector\Defluent\Skipper\FluentMethodCallSkipper $fluentMethodCallSkipper)
    {
        $this->returnFluentMethodCallFactory = $returnFluentMethodCallFactory;
        $this->fluentMethodCallsFactory = $fluentMethodCallsFactory;
        $this->separateReturnMethodCallFactory = $separateReturnMethodCallFactory;
        $this->fluentNodeRemover = $fluentNodeRemover;
        $this->methodCallSkipAnalyzer = $methodCallSkipAnalyzer;
        $this->fluentMethodCallSkipper = $fluentMethodCallSkipper;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns fluent interface calls to classic ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$someClass = new SomeClass();

return $someClass->someFunction()
        ->otherFunction();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();

return $someClass;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $methodCall = $this->matchReturnMethodCall($node);
        if (!$methodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if ($this->methodCallSkipAnalyzer->shouldSkipMethodCallIncludingNew($methodCall)) {
            return null;
        }
        if ($this->methodCallSkipAnalyzer->shouldSkipLastCallNotReturnThis($methodCall)) {
            return null;
        }
        $nodesToAdd = $this->createStandaloneNodesToAddFromReturnFluentMethodCalls($methodCall);
        if ($nodesToAdd === []) {
            return null;
        }
        $this->fluentNodeRemover->removeCurrentNode($node);
        $this->nodesToAddCollector->addNodesAfterNode($nodesToAdd, $node);
        return null;
    }
    /**
     * @return Node[]
     */
    private function createStandaloneNodesToAddFromReturnFluentMethodCalls(\PhpParser\Node\Expr\MethodCall $methodCall) : array
    {
        $fluentMethodCalls = $this->fluentMethodCallsFactory->createFromLastMethodCall($methodCall);
        if (!$fluentMethodCalls instanceof \Rector\Defluent\ValueObject\FluentMethodCalls) {
            return [];
        }
        $firstAssignFluentCall = $this->returnFluentMethodCallFactory->createFromFluentMethodCalls($fluentMethodCalls);
        if (!$firstAssignFluentCall instanceof \Rector\Defluent\ValueObject\FirstAssignFluentCall) {
            return [];
        }
        // should be skipped?
        if ($this->fluentMethodCallSkipper->shouldSkipFirstAssignFluentCall($firstAssignFluentCall)) {
            return [];
        }
        return $this->separateReturnMethodCallFactory->createReturnFromFirstAssignFluentCallAndFluentMethodCalls($firstAssignFluentCall, $fluentMethodCalls);
    }
    private function matchReturnMethodCall(\PhpParser\Node\Stmt\Return_ $return) : ?\PhpParser\Node\Expr\MethodCall
    {
        $returnExpr = $return->expr;
        if (!$returnExpr instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        return $returnExpr;
    }
}
