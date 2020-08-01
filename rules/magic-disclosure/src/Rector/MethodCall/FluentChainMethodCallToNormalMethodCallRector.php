<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\FluentChainMethodCallToNormalMethodCallRectorTest
 */
final class FluentChainMethodCallToNormalMethodCallRector extends AbstractFluentChainMethodCallRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [new CodeSample(<<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
PHP
            , <<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
PHP
        )]);
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

        // is handled by @see \Rector\MagicDisclosure\Rector\Return_\DefluentReturnMethodCallRector
        if ($this->hasParentType($node, Return_::class)) {
            return null;
        }

        // is handled by @see InArgChainMethodCallToStandaloneMethodCallRector
        if ($this->hasParentType($node, Arg::class)) {
            return null;
        }

        if (! $this->fluentChainMethodCallNodeAnalyzer->isLastChainMethodCall($methodCall)) {
            return null;
        }

        if ($this->isGetterMethodCall($methodCall)) {
            return null;
        }

        $result = $this->createStandaloneNodesToAddFromChainMethodCalls($methodCall, 'normal');
        if ($result === []) {
            return null;
        }

        [$nodesToAdd, ] = $result;
        if ($nodesToAdd === []) {
            return null;
        }

        $this->removeCurrentNode($node);

        /** @var Node[] $nodesToAdd */
        $this->addNodesAfterNode($nodesToAdd, $node);

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
     * @duplicated
     * @param MethodCall|Return_ $node
     */
    private function removeCurrentNode(Node $node): void
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            $this->removeNode($parentNode);
            return;
        }

        // part of method call
        if ($parentNode instanceof Arg) {
            $parentParent = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParent instanceof MethodCall) {
                $this->removeNode($parentParent);
            }
            return;
        }

        $this->removeNode($node);
    }

    private function isGetterMethodCall(MethodCall $methodCall): bool
    {
        if ($methodCall->var instanceof MethodCall) {
            return false;
        }
        $methodCallStaticType = $this->getStaticType($methodCall);
        $methodCallVarStaticType = $this->getStaticType($methodCall->var);

        // getter short call type
        return ! $methodCallStaticType->equals($methodCallVarStaticType);
    }
}
