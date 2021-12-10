<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Ssch\TYPO3Rector\PHPStan\TypeResolver\ArgumentTypeResolver;
final class ValidatorResolverDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{
    /**
     * @var \Ssch\TYPO3Rector\PHPStan\TypeResolver\ArgumentTypeResolver
     */
    private $argumentTypeResolver;
    public function __construct(\Ssch\TYPO3Rector\PHPStan\TypeResolver\ArgumentTypeResolver $argumentTypeResolver)
    {
        $this->argumentTypeResolver = $argumentTypeResolver;
    }
    public function getClass() : string
    {
        return 'TYPO3\\CMS\\Extbase\\Validation\\ValidatorResolver\\ValidatorResolver';
    }
    public function isMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return 'createValidator' === $methodReflection->getName();
    }
    public function getTypeFromMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\MethodCall $methodCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        return $this->argumentTypeResolver->resolveFromMethodCall($methodCall, $methodReflection);
    }
}
