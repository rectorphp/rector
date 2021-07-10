<?php

declare(strict_types=1);

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
final class ReturnFluentChainMethodCallToNormalMethodCallRector extends AbstractRector
{
    public function __construct(
        private ReturnFluentMethodCallFactory $returnFluentMethodCallFactory,
        private FluentMethodCallsFactory $fluentMethodCallsFactory,
        private SeparateReturnMethodCallFactory $separateReturnMethodCallFactory,
        private FluentNodeRemover $fluentNodeRemover,
        private MethodCallSkipAnalyzer $methodCallSkipAnalyzer,
        private FluentMethodCallSkipper $fluentMethodCallSkipper
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns fluent interface calls to classic ones.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$someClass = new SomeClass();

return $someClass->someFunction()
        ->otherFunction();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();

return $someClass;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodCall = $this->matchReturnMethodCall($node);
        if (! $methodCall instanceof MethodCall) {
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
        $this->addNodesAfterNode($nodesToAdd, $node);

        return null;
    }

    /**
     * @return Node[]
     */
    private function createStandaloneNodesToAddFromReturnFluentMethodCalls(MethodCall $methodCall): array
    {
        $fluentMethodCalls = $this->fluentMethodCallsFactory->createFromLastMethodCall($methodCall);
        if (! $fluentMethodCalls instanceof FluentMethodCalls) {
            return [];
        }

        $firstAssignFluentCall = $this->returnFluentMethodCallFactory->createFromFluentMethodCalls(
            $fluentMethodCalls
        );

        if (! $firstAssignFluentCall instanceof FirstAssignFluentCall) {
            return [];
        }

        // should be skipped?
        if ($this->fluentMethodCallSkipper->shouldSkipFirstAssignFluentCall($firstAssignFluentCall)) {
            return [];
        }

        return $this->separateReturnMethodCallFactory->createReturnFromFirstAssignFluentCallAndFluentMethodCalls(
            $firstAssignFluentCall,
            $fluentMethodCalls
        );
    }

    private function matchReturnMethodCall(Return_ $return): ?MethodCall
    {
        $returnExpr = $return->expr;
        if (! $returnExpr instanceof MethodCall) {
            return null;
        }

        return $returnExpr;
    }
}
