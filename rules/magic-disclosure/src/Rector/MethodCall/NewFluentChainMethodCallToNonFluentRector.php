<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\NodeAnalyzer\GetterMethodCallAnalyzer;
use Rector\MagicDisclosure\Rector\Return_\DefluentReturnMethodCallRector;
use Rector\MagicDisclosure\ValueObject\FluentCallsKind;

/**
 * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\FluentChainMethodCallToNormalMethodCallRectorTest
 */
final class NewFluentChainMethodCallToNonFluentRector extends AbstractFluentChainMethodCallRector
{
    /**
     * @var GetterMethodCallAnalyzer
     */
    private $getterMethodCallAnalyzer;

    public function __construct(GetterMethodCallAnalyzer $getterMethodCallAnalyzer)
    {
        $this->getterMethodCallAnalyzer = $getterMethodCallAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
(new SomeClass())->someFunction()
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
        return [MethodCall::class, Return_::class];
    }

    /**
     * @param MethodCall|Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // @todo decouple "Return_" completelly to \Rector\MagicDisclosure\Rector\Return_\DefluentReturnMethodCallRector

        $methodCall = $this->matchMethodCall($node);
        if ($methodCall === null) {
            return null;
        }

        if ($this->isHandledByAnotherRule($node)) {
            return null;
        }

        if (! $this->fluentChainMethodCallNodeAnalyzer->isLastChainMethodCall($methodCall)) {
            return null;
        }

        if ($this->getterMethodCallAnalyzer->isGetterMethodCall($methodCall)) {
            return null;
        }

        $assignAndRootExprAndNodesToAdd = $this->createStandaloneNodesToAddFromChainMethodCalls(
            $methodCall,
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
     * @param MethodCall|Return_ $node
     */
    private function matchMethodCall(Node $node): ?MethodCall
    {
        if ($node instanceof Return_) {
            if ($node->expr === null) {
                return null;
            }

            if ($node->expr instanceof MethodCall) {
                return $node->expr;
            }
            return null;
        }

        return $node;
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
