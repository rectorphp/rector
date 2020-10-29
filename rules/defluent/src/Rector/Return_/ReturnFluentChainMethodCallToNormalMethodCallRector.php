<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Defluent\NodeFactory\ReturnFluentMethodCallFactory;
use Rector\Defluent\NodeFactory\SeparateReturnMethodCallFactory;
use Rector\Defluent\Rector\AbstractFluentChainMethodCallRector;
use Rector\Defluent\ValueObjectFactory\FluentMethodCallsFactory;

/**
 * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html
 *
 * @see \Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\ReturnFluentChainMethodCallToNormalMethodCallRectorTest
 */
final class ReturnFluentChainMethodCallToNormalMethodCallRector extends AbstractFluentChainMethodCallRector
{
    /**
     * @var ReturnFluentMethodCallFactory
     */
    private $returnFluentMethodCallFactory;

    /**
     * @var FluentMethodCallsFactory
     */
    private $fluentMethodCallsFactory;

    /**
     * @var SeparateReturnMethodCallFactory
     */
    private $separateReturnMethodCallFactory;

    public function __construct(
        ReturnFluentMethodCallFactory $returnFluentMethodCallFactory,
        FluentMethodCallsFactory $fluentMethodCallsFactory,
        SeparateReturnMethodCallFactory $separateReturnMethodCallFactory
    ) {
        $this->returnFluentMethodCallFactory = $returnFluentMethodCallFactory;
        $this->fluentMethodCallsFactory = $fluentMethodCallsFactory;
        $this->separateReturnMethodCallFactory = $separateReturnMethodCallFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [
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
     * @return string[]
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
        if ($methodCall === null) {
            return null;
        }

        if ($this->shouldSkipMethodCallIncludingNew($methodCall)) {
            return null;
        }

        $nodesToAdd = $this->createStandaloneNodesToAddFromReturnFluentMethodCalls($methodCall);
        if ($nodesToAdd === []) {
            return null;
        }

        $this->removeCurrentNode($node);
        $this->addNodesAfterNode($nodesToAdd, $node);

        return null;
    }

    protected function shouldSkipMethodCallIncludingNew(MethodCall $methodCall): bool
    {
        if ($this->shouldSkipMethodCall($methodCall)) {
            return true;
        }

        $rootVariable = $this->fluentChainMethodCallNodeAnalyzer->resolveRootExpr($methodCall);
        return $rootVariable instanceof New_;
    }

    /**
     * @return Node[]
     */
    private function createStandaloneNodesToAddFromReturnFluentMethodCalls(MethodCall $methodCall): array
    {
        $fluentMethodCalls = $this->fluentMethodCallsFactory->createFromLastMethodCall($methodCall);
        if ($fluentMethodCalls === null) {
            return [];
        }

        $firstAssignFluentCall = $this->returnFluentMethodCallFactory->createFromFluentMethodCalls(
            $fluentMethodCalls
        );

        if ($firstAssignFluentCall === null) {
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
}
