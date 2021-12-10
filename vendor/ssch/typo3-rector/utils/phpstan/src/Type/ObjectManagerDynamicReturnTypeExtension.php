<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Ssch\TYPO3Rector\PHPStan\TypeResolver\ArgumentTypeResolver;
final class ObjectManagerDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
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
        return 'TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface';
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection $methodReflection
     */
    public function isMethodSupported($methodReflection) : bool
    {
        return 'get' === $methodReflection->getName();
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection $methodReflection
     * @param \PhpParser\Node\Expr\MethodCall $methodCall
     * @param \PHPStan\Analyser\Scope $scope
     */
    public function getTypeFromMethodCall($methodReflection, $methodCall, $scope) : \PHPStan\Type\Type
    {
        return $this->argumentTypeResolver->resolveFromMethodCall($methodCall, $methodReflection);
    }
}
