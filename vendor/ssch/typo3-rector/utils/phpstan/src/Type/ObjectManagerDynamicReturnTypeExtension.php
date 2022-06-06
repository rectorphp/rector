<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\PHPStan\Type;

use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Type\DynamicMethodReturnTypeExtension;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Ssch\TYPO3Rector\PHPStan\TypeResolver\ArgumentTypeResolver;
final class ObjectManagerDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\PHPStan\TypeResolver\ArgumentTypeResolver
     */
    private $argumentTypeResolver;
    public function __construct(ArgumentTypeResolver $argumentTypeResolver)
    {
        $this->argumentTypeResolver = $argumentTypeResolver;
    }
    public function getClass() : string
    {
        return 'TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface';
    }
    public function isMethodSupported(MethodReflection $methodReflection) : bool
    {
        return 'get' === $methodReflection->getName();
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : ?\RectorPrefix20220606\PHPStan\Type\Type
    {
        return $this->argumentTypeResolver->resolveFromMethodCall($methodCall, $methodReflection);
    }
}
