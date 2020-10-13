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
use Rector\NetteKdyby\Naming\VariableNaming;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\MethodCallOnSetterMethodCallToStandaloneAssignRectorTest
 */
final class MethodCallOnSetterMethodCallToStandaloneAssignRector extends AbstractFluentChainMethodCallRector
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
        return new RectorDefinition('Change method call on setter to standalone assign before the setter', [
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
        if ($rootMethodCall === null) {
            return null;
        }

        $new = $this->newFluentChainMethodCallNodeAnalyzer->matchNewInFluentSetterMethodCall($rootMethodCall);
        if ($new === null) {
            return null;
        }

        $newStmts = $this->nonFluentChainMethodCallFactory->createFromNewAndRootMethodCall($new, $node);
        $this->addNodesBeforeNode($newStmts, $node);

        // change new arg to root variable
        $newVariable = $this->crateVariableFromNew($new);
        $rootMethodCall->args = [new Arg($newVariable)];

        return $rootMethodCall;
    }

    private function crateVariableFromNew(New_ $new): Variable
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        return new Variable($variableName);
    }
}
