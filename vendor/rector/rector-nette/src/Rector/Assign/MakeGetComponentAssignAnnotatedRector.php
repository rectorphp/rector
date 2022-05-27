<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Assign;

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
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\MakeGetComponentAssignAnnotatedRectorTest
 */
final class MakeGetComponentAssignAnnotatedRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator
     */
    private $varAnnotationManipulator;
    public function __construct(VarAnnotationManipulator $varAnnotationManipulator)
    {
        $this->varAnnotationManipulator = $varAnnotationManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add doc type for magic $control->getComponent(...) assign', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isGetComponentMethodCallOrArrayDimFetchOnControl($node->expr)) {
            return null;
        }
        if (!$node->var instanceof Variable) {
            return null;
        }
        $variableName = $this->getName($node->var);
        if ($variableName === null) {
            return null;
        }
        $nodeVar = $this->nodeTypeResolver->getType($node->var);
        if (!$nodeVar instanceof MixedType) {
            return null;
        }
        $controlType = $this->resolveControlType($node);
        if (!$controlType instanceof TypeWithClassName) {
            return null;
        }
        $this->varAnnotationManipulator->decorateNodeWithInlineVarType($node, $controlType, $variableName);
        return $node;
    }
    private function isGetComponentMethodCallOrArrayDimFetchOnControl(Expr $expr) : bool
    {
        if (!$expr instanceof MethodCall) {
            return $this->isArrayDimFetchStringOnControlVariable($expr);
        }
        if (!$this->isObjectType($expr->var, new ObjectType('Nette\\Application\\UI\\Control'))) {
            return $this->isArrayDimFetchStringOnControlVariable($expr);
        }
        if (!$this->isName($expr->name, 'getComponent')) {
            return $this->isArrayDimFetchStringOnControlVariable($expr);
        }
        return \true;
    }
    private function resolveControlType(Assign $assign) : Type
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
    private function isArrayDimFetchStringOnControlVariable(Expr $expr) : bool
    {
        if (!$expr instanceof ArrayDimFetch) {
            return \false;
        }
        if (!$expr->dim instanceof String_) {
            return \false;
        }
        $varStaticType = $this->getType($expr->var);
        if (!$varStaticType instanceof TypeWithClassName) {
            return \false;
        }
        $controlObjecType = new ObjectType('Nette\\Application\\UI\\Control');
        return $controlObjecType->isSuperTypeOf($varStaticType)->yes();
    }
    private function resolveCreateComponentMethodCallReturnType(MethodCall $methodCall) : Type
    {
        $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        if (\count($methodCall->args) !== 1) {
            return new MixedType();
        }
        $firstArgumentValue = $methodCall->args[0]->value;
        if (!$firstArgumentValue instanceof String_) {
            return new MixedType();
        }
        return $this->resolveTypeFromShortControlNameAndVariable($firstArgumentValue, $scope, $methodCall->var);
    }
    private function resolveArrayDimFetchControlType(ArrayDimFetch $arrayDimFetch) : Type
    {
        $scope = $arrayDimFetch->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            throw new ShouldNotHappenException();
        }
        if (!$arrayDimFetch->dim instanceof String_) {
            return new MixedType();
        }
        return $this->resolveTypeFromShortControlNameAndVariable($arrayDimFetch->dim, $scope, $arrayDimFetch->var);
    }
    private function resolveTypeFromShortControlNameAndVariable(String_ $shortControlString, Scope $scope, Expr $expr) : Type
    {
        $componentName = $this->valueResolver->getValue($shortControlString);
        if (!\is_string($componentName)) {
            throw new ShouldNotHappenException();
        }
        $componentName = \ucfirst($componentName);
        $methodName = \sprintf('createComponent%s', $componentName);
        $calledOnType = $scope->getType($expr);
        if (!$calledOnType instanceof TypeWithClassName) {
            return new MixedType();
        }
        if (!$calledOnType->hasMethod($methodName)->yes()) {
            return new MixedType();
        }
        // has method
        $methodReflection = $calledOnType->getMethod($methodName, $scope);
        return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
    }
}
