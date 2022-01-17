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
final class MakeGetComponentAssignAnnotatedRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator
     */
    private $varAnnotationManipulator;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator $varAnnotationManipulator)
    {
        $this->varAnnotationManipulator = $varAnnotationManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add doc type for magic $control->getComponent(...) assign', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isGetComponentMethodCallOrArrayDimFetchOnControl($node->expr)) {
            return null;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $variableName = $this->getName($node->var);
        if ($variableName === null) {
            return null;
        }
        $nodeVar = $this->nodeTypeResolver->getType($node->var);
        if (!$nodeVar instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        $controlType = $this->resolveControlType($node);
        if (!$controlType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $this->varAnnotationManipulator->decorateNodeWithInlineVarType($node, $controlType, $variableName);
        return $node;
    }
    private function isGetComponentMethodCallOrArrayDimFetchOnControl(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->isArrayDimFetchStringOnControlVariable($expr);
        }
        if (!$this->isObjectType($expr->var, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control'))) {
            return $this->isArrayDimFetchStringOnControlVariable($expr);
        }
        if (!$this->isName($expr->name, 'getComponent')) {
            return $this->isArrayDimFetchStringOnControlVariable($expr);
        }
        return \true;
    }
    private function resolveControlType(\PhpParser\Node\Expr\Assign $assign) : \PHPStan\Type\Type
    {
        if ($assign->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            /** @var MethodCall $methodCall */
            $methodCall = $assign->expr;
            return $this->resolveCreateComponentMethodCallReturnType($methodCall);
        }
        if ($assign->expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            /** @var ArrayDimFetch $arrayDimFetch */
            $arrayDimFetch = $assign->expr;
            return $this->resolveArrayDimFetchControlType($arrayDimFetch);
        }
        return new \PHPStan\Type\MixedType();
    }
    private function isArrayDimFetchStringOnControlVariable(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        if (!$expr->dim instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        $varStaticType = $this->getType($expr->var);
        if (!$varStaticType instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        $controlObjecType = new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control');
        return $controlObjecType->isSuperTypeOf($varStaticType)->yes();
    }
    private function resolveCreateComponentMethodCallReturnType(\PhpParser\Node\Expr\MethodCall $methodCall) : \PHPStan\Type\Type
    {
        $scope = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \PHPStan\Type\MixedType();
        }
        if (\count($methodCall->args) !== 1) {
            return new \PHPStan\Type\MixedType();
        }
        $firstArgumentValue = $methodCall->args[0]->value;
        if (!$firstArgumentValue instanceof \PhpParser\Node\Scalar\String_) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->resolveTypeFromShortControlNameAndVariable($firstArgumentValue, $scope, $methodCall->var);
    }
    private function resolveArrayDimFetchControlType(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : \PHPStan\Type\Type
    {
        $scope = $arrayDimFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if (!$arrayDimFetch->dim instanceof \PhpParser\Node\Scalar\String_) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->resolveTypeFromShortControlNameAndVariable($arrayDimFetch->dim, $scope, $arrayDimFetch->var);
    }
    private function resolveTypeFromShortControlNameAndVariable(\PhpParser\Node\Scalar\String_ $shortControlString, \PHPStan\Analyser\Scope $scope, \PhpParser\Node\Expr $expr) : \PHPStan\Type\Type
    {
        $componentName = $this->valueResolver->getValue($shortControlString);
        if (!\is_string($componentName)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $componentName = \ucfirst($componentName);
        $methodName = \sprintf('createComponent%s', $componentName);
        $calledOnType = $scope->getType($expr);
        if (!$calledOnType instanceof \PHPStan\Type\TypeWithClassName) {
            return new \PHPStan\Type\MixedType();
        }
        if (!$calledOnType->hasMethod($methodName)->yes()) {
            return new \PHPStan\Type\MixedType();
        }
        // has method
        $methodReflection = $calledOnType->getMethod($methodName, $scope);
        return \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
    }
}
