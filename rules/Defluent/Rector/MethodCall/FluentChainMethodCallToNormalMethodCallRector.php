<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\Matcher\AssignAndRootExprAndNodesToAddMatcher;
use Rector\Defluent\NodeAnalyzer\MethodCallSkipAnalyzer;
use Rector\Defluent\Rector\Return_\DefluentReturnMethodCallRector;
use Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\NodeAnalyzer\FluentNodeRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 *
 * @see \Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\FluentChainMethodCallToNormalMethodCallRectorTest
 */
final class FluentChainMethodCallToNormalMethodCallRector extends AbstractRector
{
    public function __construct(
        private FluentNodeRemover $fluentNodeRemover,
        private MethodCallSkipAnalyzer $methodCallSkipAnalyzer,
        private AssignAndRootExprAndNodesToAddMatcher $assignAndRootExprAndNodesToAddMatcher
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns fluent interface calls to classic ones.',
            [
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
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
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

        if ($this->methodCallSkipAnalyzer->shouldSkipMethodCallIncludingNew($node)) {
            return null;
        }

        $assignAndRootExprAndNodesToAdd = $this->assignAndRootExprAndNodesToAddMatcher->match(
            $node,
            FluentCallsKind::NORMAL
        );

        if (! $assignAndRootExprAndNodesToAdd instanceof AssignAndRootExprAndNodesToAdd) {
            return null;
        }

        $currentStatement = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $nodesToAdd = $assignAndRootExprAndNodesToAdd->getNodesToAdd();

        if ($currentStatement instanceof Return_) {
            $lastNodeToAdd = end($nodesToAdd);

            if (! $lastNodeToAdd) {
                return null;
            }

            if (! $lastNodeToAdd instanceof Return_) {
                $nodesToAdd[array_key_last($nodesToAdd)] = new Return_($lastNodeToAdd);
            }
        }

        $this->removeCurrentNode($node);
        $this->addNodesAfterNode($nodesToAdd, $node);

        return null;
    }

    private function removeCurrentNode(MethodCall $methodCall): void
    {
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        while ($parent instanceof Cast) {
            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parent instanceof Cast) {
                $this->fluentNodeRemover->removeCurrentNode($parent);
                return;
            }
        }

        $this->fluentNodeRemover->removeCurrentNode($methodCall);
    }

    /**
     * Is handled by:
     * @see DefluentReturnMethodCallRector
     * @see InArgFluentChainMethodCallToStandaloneMethodCallRector
     */
    private function isHandledByAnotherRule(MethodCall $methodCall): bool
    {
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Return_) {
            return true;
        }

        return $parent instanceof Arg;
    }
}
