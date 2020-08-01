<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\NodeAnalyzer\ChainMethodCallNodeAnalyzer;
use Rector\NetteKdyby\Naming\VariableNaming;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\MethodCallOnSetterMethodCallToStandaloneAssignRectorTest
 */
final class MethodCallOnSetterMethodCallToStandaloneAssignRector extends AbstractRector
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    /**
     * @var ChainMethodCallNodeAnalyzer
     */
    private $chainMethodCallNodeAnalyzer;

    public function __construct(
        VariableNaming $variableNaming,
        ChainMethodCallNodeAnalyzer $chainMethodCallNodeAnalyzer
    ) {
        $this->variableNaming = $variableNaming;
        $this->chainMethodCallNodeAnalyzer = $chainMethodCallNodeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change method call on setter to standalone assign before the setter', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
PHP
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $rootMethodCall = $this->chainMethodCallNodeAnalyzer->resolveRootMethodCall($node);
        if ($rootMethodCall === null) {
            return null;
        }

        $new = $this->matchNewInFluentSetterMethodCall($rootMethodCall);
        if ($new === null) {
            return null;
        }

        $variableName = $this->variableNaming->resolveFromNode($new);
        $newVariable = new Variable($variableName);

        $assignExpression = $this->createAssignExpression($newVariable, $new);
        $this->addNodeBeforeNode($assignExpression, $node);

        // resolve chain calls
        $chainMethodCalls = $this->chainMethodCallNodeAnalyzer->collectAllMethodCallsInChainWithoutRootOne($node);
        $chainMethodCalls = array_reverse($chainMethodCalls);
        foreach ($chainMethodCalls as $chainMethodCall) {
            $currentMethodCall = new MethodCall($newVariable, $chainMethodCall->name, $chainMethodCall->args);
            $this->addNodeBeforeNode($currentMethodCall, $node);
        }

        // change new arg to root variable
        $rootMethodCall->args = [new Arg($newVariable)];

        return $rootMethodCall;
    }

    /**
     * Method call with "new X", that returns "X"?
     * e.g.
     *
     * $this->setItem(new Item) // → returns "Item"
     */
    private function matchNewInFluentSetterMethodCall(MethodCall $methodCall): ?New_
    {
        if (count($methodCall->args) !== 1) {
            return null;
        }

        $onlyArgValue = $methodCall->args[0]->value;
        if (! $onlyArgValue instanceof New_) {
            return null;
        }

        $newType = $this->getObjectType($onlyArgValue);
        if ($newType instanceof MixedType) {
            return null;
        }

        $parentMethodCallReturnType = $this->getObjectType($methodCall);
        if (! $newType->equals($parentMethodCallReturnType)) {
            return null;
        }

        return $onlyArgValue;
    }

    private function createAssignExpression(Variable $newVariable, New_ $new): Expression
    {
        $assign = new Assign($newVariable, $new);
        return new Expression($assign);
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $methodCall->var instanceof MethodCall) {
            return true;
        }

        return ! $this->chainMethodCallNodeAnalyzer->isLastChainMethodCall($methodCall);
    }
}
