<?php

declare (strict_types=1);
namespace PHPStan\Type\PHPUnit\Assert;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
class AssertMethodTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /** @var TypeSpecifier */
    private $typeSpecifier;
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier) : void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
    public function getClass() : string
    {
        return 'RectorPrefix202301\\PHPUnit\\Framework\\Assert';
    }
    public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context) : bool
    {
        return \PHPStan\Type\PHPUnit\Assert\AssertTypeSpecifyingExtensionHelper::isSupported($methodReflection->getName(), $node->getArgs());
    }
    public function specifyTypes(MethodReflection $functionReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context) : SpecifiedTypes
    {
        return \PHPStan\Type\PHPUnit\Assert\AssertTypeSpecifyingExtensionHelper::specifyTypes($this->typeSpecifier, $scope, $functionReflection->getName(), $node->getArgs());
    }
}
