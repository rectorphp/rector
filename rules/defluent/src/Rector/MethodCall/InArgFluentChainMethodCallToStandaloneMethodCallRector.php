<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Defluent\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\Rector\AbstractFluentChainMethodCallRector;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest
 */
final class InArgFluentChainMethodCallToStandaloneMethodCallRector extends AbstractFluentChainMethodCallRector
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    /**
     * @var NewFluentChainMethodCallNodeAnalyzer
     */
    private $newFluentChainMethodCallNodeAnalyzer;

    public function __construct(
        VariableNaming $variableNaming,
        NewFluentChainMethodCallNodeAnalyzer $newFluentChainMethodCallNodeAnalyzer
    ) {
        $this->variableNaming = $variableNaming;
        $this->newFluentChainMethodCallNodeAnalyzer = $newFluentChainMethodCallNodeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [new CodeSample(<<<'CODE_SAMPLE'
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
        )]);
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
        if (! $this->hasParentType($node, Arg::class)) {
            return null;
        }

        /** @var Arg $arg */
        $arg = $node->getAttribute(AttributeKey::PARENT_NODE);
        /** @var Node|null $parentMethodCall */
        $parentMethodCall = $arg->getAttribute(AttributeKey::PARENT_NODE);
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

        if ($assignAndRootExprAndNodesToAdd === null) {
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

        $newVariable = $this->crateVariableFromNew($new);
        $nodesToAdd[] = $this->createFluentAsArg($methodCall, $newVariable);

        $this->addNodesBeforeNode($nodesToAdd, $methodCall);
        $this->removeParentParent($methodCall);
    }

    private function crateVariableFromNew(New_ $new): Variable
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        return new Variable($variableName);
    }

    /**
     * @deprecated
     * @todo extact to factory
     */
    private function createFluentAsArg(MethodCall $methodCall, Variable $variable): MethodCall
    {
        /** @var Arg $parent */
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        /** @var MethodCall $parentParent */
        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);

        $lastMethodCall = new MethodCall($parentParent->var, $parentParent->name);
        $lastMethodCall->args[] = new Arg($variable);

        return $lastMethodCall;
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
