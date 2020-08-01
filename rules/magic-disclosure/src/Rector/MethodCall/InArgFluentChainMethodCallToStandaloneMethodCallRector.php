<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest
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
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [new CodeSample(<<<'PHP'
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

PHP
            , <<<'PHP'
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
PHP
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

        $result = $this->createStandaloneNodesToAddFromChainMethodCalls($node, 'in_args');
        if ($result === []) {
            return null;
        }

        [$nodesToAdd, $assignAndRootExpr] = $result;

        /** @var Node[] $nodesToAdd */
        $this->addNodesBeforeNode($nodesToAdd, $node);

        /** @var AssignAndRootExpr $assignAndRootExpr */
        return $assignAndRootExpr->getCallerExpr();
    }

    private function removeParentParent(MethodCall $methodCall): void
    {
        /** @var Arg $parent */
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);

        /** @var MethodCall $parentParent */
        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);

        $this->removeNode($parentParent);
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

    private function crateVariableFromNew(New_ $new): Variable
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        return new Variable($variableName);
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
}
