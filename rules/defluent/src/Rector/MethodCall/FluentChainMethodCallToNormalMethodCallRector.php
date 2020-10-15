<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Defluent\Rector\AbstractFluentChainMethodCallRector;
use Rector\Defluent\Rector\Return_\DefluentReturnMethodCallRector;
use Rector\Defluent\ValueObject\FluentCallsKind;

/**
 * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html
 *
 * @see \Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\FluentChainMethodCallToNormalMethodCallRectorTest
 */
final class FluentChainMethodCallToNormalMethodCallRector extends AbstractFluentChainMethodCallRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isHandledByAnotherRule($node)) {
            return null;
        }

        if ($this->shouldSkipMethodCallIncludingNew($node)) {
            return null;
        }

        $assignAndRootExprAndNodesToAdd = $this->createStandaloneNodesToAddFromChainMethodCalls(
            $node,
            FluentCallsKind::NORMAL
        );

        if ($assignAndRootExprAndNodesToAdd === null) {
            return null;
        }

        $this->removeCurrentNode($node);
        $this->addNodesAfterNode($assignAndRootExprAndNodesToAdd->getNodesToAdd(), $node);

        return null;
    }

    /**
     * Is handled by:
     * @see DefluentReturnMethodCallRector
     * @see InArgFluentChainMethodCallToStandaloneMethodCallRector
     *
     * @param MethodCall|Return_ $node
     */
    private function isHandledByAnotherRule(Node $node): bool
    {
        return $this->hasParentTypes($node, [Return_::class, Arg::class]);
    }
}
