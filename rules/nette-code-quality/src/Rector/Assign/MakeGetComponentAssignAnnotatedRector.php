<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\NetteCodeQuality\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\MakeGetComponentAssignAnnotatedRectorTest
 */
final class MakeGetComponentAssignAnnotatedRector extends AbstractRector
{
    /**
     * @var VarAnnotationManipulator
     */
    private $varAnnotationManipulator;

    public function __construct(VarAnnotationManipulator $varAnnotationManipulator)
    {
        $this->varAnnotationManipulator = $varAnnotationManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add doc type for magic $control->getComponent(...) assign',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        $anotherControl = $externalControl->getComponent('another');
    }
}

final class ExternalControl extends Control
{
    public function createComponentAnother(): AnotherControl
    {
        return new AnotherControl();
    }
}

final class AnotherControl extends Control
{
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        /** @var AnotherControl $anotherControl */
        $anotherControl = $externalControl->getComponent('another');
    }
}

final class ExternalControl extends Control
{
    public function createComponentAnother(): AnotherControl
    {
        return new AnotherControl();
    }
}

final class AnotherControl extends Control
{
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isGetComponentMethodCallOrArrayDimFetchOnControl($node->expr)) {
            return null;
        }

        if (! $node->var instanceof Variable) {
            return null;
        }

        $variableName = $this->getName($node->var);
        if ($variableName === null) {
            return null;
        }

        $nodeVar = $this->getObjectType($node->var);
        if (! $nodeVar instanceof MixedType) {
            return null;
        }

        $controlType = $this->resolveControlType($node);
        if (! $controlType instanceof TypeWithClassName) {
            return null;
        }

        $this->varAnnotationManipulator->decorateNodeWithInlineVarType($node, $controlType, $variableName);

        return $node;
    }

    private function isGetComponentMethodCallOrArrayDimFetchOnControl(Expr $expr): bool
    {
        if ($this->isOnClassMethodCall($expr, 'Nette\Application\UI\Control', 'getComponent')) {
            return true;
        }

        return $this->isArrayDimFetchStringOnControlVariable($expr);
    }

    private function resolveControlType(Assign $assign): Type
    {
        if ($assign->expr instanceof MethodCall) {
            /** @var MethodCall $methodCall */
            $methodCall = $assign->expr;
            return $this->resolveCreateComponentMethodCallReturnType($methodCall);
        }

        if ($assign->expr instanceof ArrayDimFetch) {
            /** @var ArrayDimFetch $arrayDimFetch */
            $arrayDimFetch = $assign->expr;
            return $this->resolveArrayDimFetchControlType($arrayDimFetch);
        }

        return new MixedType();
    }

    private function isArrayDimFetchStringOnControlVariable(Expr $expr): bool
    {
        if (! $expr instanceof ArrayDimFetch) {
            return false;
        }

        if (! $expr->dim instanceof String_) {
            return false;
        }

        $varStaticType = $this->getStaticType($expr->var);
        if (! $varStaticType instanceof TypeWithClassName) {
            return false;
        }

        return is_a($varStaticType->getClassName(), 'Nette\Application\UI\Control', true);
    }

    private function resolveCreateComponentMethodCallReturnType(MethodCall $methodCall): Type
    {
        $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return new MixedType();
        }

        if (count($methodCall->args) !== 1) {
            return new MixedType();
        }

        $firstArgumentValue = $methodCall->args[0]->value;
        if (! $firstArgumentValue instanceof String_) {
            return new MixedType();
        }

        return $this->resolveTypeFromShortControlNameAndVariable($firstArgumentValue, $scope, $methodCall->var);
    }

    private function resolveArrayDimFetchControlType(ArrayDimFetch $arrayDimFetch): Type
    {
        $scope = $arrayDimFetch->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            throw new ShouldNotHappenException();
        }

        if (! $arrayDimFetch->dim instanceof String_) {
            return new MixedType();
        }

        return $this->resolveTypeFromShortControlNameAndVariable($arrayDimFetch->dim, $scope, $arrayDimFetch->var);
    }

    private function resolveTypeFromShortControlNameAndVariable(
        String_ $shortControlString,
        Scope $scope,
        Expr $expr
    ): Type {
        $componentName = $this->valueResolver->getValue($shortControlString);
        $componentName = ucfirst($componentName);

        $methodName = sprintf('createComponent%s', $componentName);

        $calledOnType = $scope->getType($expr);
        if (! $calledOnType instanceof TypeWithClassName) {
            return new MixedType();
        }

        if (! $calledOnType->hasMethod($methodName)->yes()) {
            return new MixedType();
        }

        // has method
        $method = $calledOnType->getMethod($methodName, $scope);

        return ParametersAcceptorSelector::selectSingle($method->getVariants())->getReturnType();
    }
}
