<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\Defluent\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeFactory\FluentMethodCallAsArgFactory;
use Rector\Defluent\NodeFactory\VariableFromNewFactory;
use Rector\Defluent\Rector\AbstractFluentChainMethodCallRector;
use Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest
 */
final class InArgFluentChainMethodCallToStandaloneMethodCallRector extends AbstractFluentChainMethodCallRector
{
    /**
     * @var NewFluentChainMethodCallNodeAnalyzer
     */
    private $newFluentChainMethodCallNodeAnalyzer;

    /**
     * @var VariableFromNewFactory
     */
    private $variableFromNewFactory;

    /**
     * @var FluentMethodCallAsArgFactory
     */
    private $fluentMethodCallAsArgFactory;

    public function __construct(
        NewFluentChainMethodCallNodeAnalyzer $newFluentChainMethodCallNodeAnalyzer,
        VariableFromNewFactory $variableFromNewFactory,
        FluentMethodCallAsArgFactory $fluentMethodCallAsArgFactory
    ) {
        $this->newFluentChainMethodCallNodeAnalyzer = $newFluentChainMethodCallNodeAnalyzer;
        $this->variableFromNewFactory = $variableFromNewFactory;
        $this->fluentMethodCallAsArgFactory = $fluentMethodCallAsArgFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns fluent interface calls to classic ones.',
            [
                new CodeSample(<<<'CODE_SAMPLE'
class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $this->processFluentClass($someClass->someFunction()->otherFunction());
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}

CODE_SAMPLE
                , <<<'CODE_SAMPLE'
class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $someClass->someFunction();
        $someClass->otherFunction();
        $this->processFluentClass($someClass);
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}
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
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Arg) {
            return null;
        }

        $parentMethodCall = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentMethodCall instanceof MethodCall) {
            return null;
        }

        if (! $this->fluentChainMethodCallNodeAnalyzer->isLastChainMethodCall($node)) {
            return null;
        }

        // create instances from (new ...)->call, re-use from
        if ($node->var instanceof New_) {
            $this->refactorNew($node, $node->var);
            return null;
        }

        $assignAndRootExprAndNodesToAdd = $this->createStandaloneNodesToAddFromChainMethodCalls(
            $node,
            FluentCallsKind::IN_ARGS
        );

        if (! $assignAndRootExprAndNodesToAdd instanceof AssignAndRootExprAndNodesToAdd) {
            return null;
        }

        $this->addNodesBeforeNode($assignAndRootExprAndNodesToAdd->getNodesToAdd(), $node);
        return $assignAndRootExprAndNodesToAdd->getRootCallerExpr();
    }

    private function refactorNew(MethodCall $methodCall, New_ $new): void
    {
        if (! $this->newFluentChainMethodCallNodeAnalyzer->isNewMethodCallReturningSelf($methodCall)) {
            return;
        }

        $nodesToAdd = $this->nonFluentChainMethodCallFactory->createFromNewAndRootMethodCall($new, $methodCall);

        $newVariable = $this->variableFromNewFactory->create($new);
        $nodesToAdd[] = $this->fluentMethodCallAsArgFactory->createFluentAsArg($methodCall, $newVariable);

        $this->addNodesBeforeNode($nodesToAdd, $methodCall);
        $this->removeParentParent($methodCall);
    }

    private function removeParentParent(MethodCall $methodCall): void
    {
        /** @var Arg $parent */
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);

        /** @var MethodCall $parentParent */
        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);

        $this->removeNode($parentParent);
    }
}
