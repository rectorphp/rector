<?php

declare (strict_types=1);
namespace PHPStan\Type\PHPUnit\Assert;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
class AssertFunctionTypeSpecifyingExtension implements \PHPStan\Type\FunctionTypeSpecifyingExtension, \PHPStan\Analyser\TypeSpecifierAwareExtension
{
    /** @var TypeSpecifier */
    private $typeSpecifier;
    public function setTypeSpecifier(\PHPStan\Analyser\TypeSpecifier $typeSpecifier) : void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
    public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $node, \PHPStan\Analyser\TypeSpecifierContext $context) : bool
    {
        return \PHPStan\Type\PHPUnit\Assert\AssertTypeSpecifyingExtensionHelper::isSupported($this->trimName($functionReflection->getName()), $node->args);
    }
    public function specifyTypes(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $node, \PHPStan\Analyser\Scope $scope, \PHPStan\Analyser\TypeSpecifierContext $context) : \PHPStan\Analyser\SpecifiedTypes
    {
        return \PHPStan\Type\PHPUnit\Assert\AssertTypeSpecifyingExtensionHelper::specifyTypes($this->typeSpecifier, $scope, $this->trimName($functionReflection->getName()), $node->args);
    }
    private function trimName(string $functionName) : string
    {
        $prefix = 'PHPUnit\\Framework\\';
        if (\strpos($functionName, $prefix) === 0) {
            return \substr($functionName, \strlen($prefix));
        }
        return $functionName;
    }
}
