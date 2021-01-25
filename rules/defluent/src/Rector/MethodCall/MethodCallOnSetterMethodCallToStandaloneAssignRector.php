<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Defluent\NodeAnalyzer\NewFluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeFactory\VariableFromNewFactory;
use Rector\Defluent\Rector\AbstractFluentChainMethodCallRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\MethodCallOnSetterMethodCallToStandaloneAssignRectorTest
 */
final class MethodCallOnSetterMethodCallToStandaloneAssignRector extends AbstractFluentChainMethodCallRector
{
    /**
     * @var NewFluentChainMethodCallNodeAnalyzer
     */
    private $newFluentChainMethodCallNodeAnalyzer;

    /**
     * @var VariableFromNewFactory
     */
    private $variableFromNewFactory;

    public function __construct(
        NewFluentChainMethodCallNodeAnalyzer $newFluentChainMethodCallNodeAnalyzer,
        VariableFromNewFactory $variableFromNewFactory
    ) {
        $this->newFluentChainMethodCallNodeAnalyzer = $newFluentChainMethodCallNodeAnalyzer;
        $this->variableFromNewFactory = $variableFromNewFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change method call on setter to standalone assign before the setter',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function some()
    {
        $this->anotherMethod(new AnotherClass())
            ->someFunction();
    }

    public function anotherMethod(AnotherClass $anotherClass)
    {
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function some()
    {
        $anotherClass = new AnotherClass();
        $anotherClass->someFunction();
        $this->anotherMethod($anotherClass);
    }

    public function anotherMethod(AnotherClass $anotherClass)
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
        if ($this->shouldSkipMethodCall($node)) {
            return null;
        }

        $rootMethodCall = $this->fluentChainMethodCallNodeAnalyzer->resolveRootMethodCall($node);
        if (! $rootMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }

        $new = $this->newFluentChainMethodCallNodeAnalyzer->matchNewInFluentSetterMethodCall($rootMethodCall);
        if (! $new instanceof New_) {
            return null;
        }

        $newStmts = $this->nonFluentChainMethodCallFactory->createFromNewAndRootMethodCall($new, $node);
        $this->addNodesBeforeNode($newStmts, $node);

        // change new arg to root variable
        $newVariable = $this->variableFromNewFactory->create($new);
        $rootMethodCall->args = [new Arg($newVariable)];

        return $rootMethodCall;
    }
}
