<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\Type\PHPUnit\Assert;

use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Analyser\SpecifiedTypes;
use RectorPrefix20220606\PHPStan\Analyser\TypeSpecifier;
use RectorPrefix20220606\PHPStan\Analyser\TypeSpecifierAwareExtension;
use RectorPrefix20220606\PHPStan\Analyser\TypeSpecifierContext;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Type\StaticMethodTypeSpecifyingExtension;
class AssertStaticMethodTypeSpecifyingExtension implements StaticMethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /** @var TypeSpecifier */
    private $typeSpecifier;
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier) : void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
    public function getClass() : string
    {
        return 'RectorPrefix20220606\\PHPUnit\\Framework\\Assert';
    }
    public function isStaticMethodSupported(MethodReflection $methodReflection, StaticCall $node, TypeSpecifierContext $context) : bool
    {
        return AssertTypeSpecifyingExtensionHelper::isSupported($methodReflection->getName(), $node->getArgs());
    }
    public function specifyTypes(MethodReflection $functionReflection, StaticCall $node, Scope $scope, TypeSpecifierContext $context) : SpecifiedTypes
    {
        return AssertTypeSpecifyingExtensionHelper::specifyTypes($this->typeSpecifier, $scope, $functionReflection->getName(), $node->getArgs());
    }
}
