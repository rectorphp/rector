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
use Rector\NetteKdyby\Naming\VariableNaming;

/**
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\SetterOnSetterMethodCallToStandaloneAssignRector\SetterOnSetterMethodCallToStandaloneAssignRectorTest
 */
final class SetterOnSetterMethodCallToStandaloneAssignRector extends AbstractRector
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
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
        if (! $node->var instanceof MethodCall) {
            return null;
        }

        $parentMethodCall = $node->var;
        $new = $this->matchNewInFluentSetterMethodCall($parentMethodCall);
        if ($new === null) {
            return null;
        }

        $variableName = $this->variableNaming->resolveFromNode($new);
        $newVariable = new Variable($variableName);

        $assignExpression = $this->createAssignExpression($newVariable, $new);
        $this->addNodeBeforeNode($assignExpression, $node);

        $currentMethodCall = new MethodCall($newVariable, $node->name, $node->args);
        $this->addNodeBeforeNode($currentMethodCall, $node);

        // change new arg to variable
        $parentMethodCall->args = [new Arg($newVariable)];

        return $parentMethodCall;
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
}
