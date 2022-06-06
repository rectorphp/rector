<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\Type\PHPUnit\Assert;

use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Analyser\SpecifiedTypes;
use RectorPrefix20220606\PHPStan\Analyser\TypeSpecifier;
use RectorPrefix20220606\PHPStan\Analyser\TypeSpecifierAwareExtension;
use RectorPrefix20220606\PHPStan\Analyser\TypeSpecifierContext;
use RectorPrefix20220606\PHPStan\Reflection\FunctionReflection;
use RectorPrefix20220606\PHPStan\Type\FunctionTypeSpecifyingExtension;
use function strlen;
use function strpos;
use function substr;
class AssertFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /** @var TypeSpecifier */
    private $typeSpecifier;
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier) : void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
    public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, TypeSpecifierContext $context) : bool
    {
        return AssertTypeSpecifyingExtensionHelper::isSupported($this->trimName($functionReflection->getName()), $node->getArgs());
    }
    public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context) : SpecifiedTypes
    {
        return AssertTypeSpecifyingExtensionHelper::specifyTypes($this->typeSpecifier, $scope, $this->trimName($functionReflection->getName()), $node->getArgs());
    }
    private function trimName(string $functionName) : string
    {
        $prefix = 'PHPUnit\\Framework\\';
        if (strpos($functionName, $prefix) === 0) {
            return substr($functionName, strlen($prefix));
        }
        return $functionName;
    }
}
