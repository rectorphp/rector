<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\Type;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class ContextGetAspectDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return 'TYPO3\\CMS\\Core\\Context\\Context';
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection $methodReflection
     */
    public function isMethodSupported($methodReflection) : bool
    {
        return 'getAspect' === $methodReflection->getName();
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection $methodReflection
     * @param \PhpParser\Node\Expr\MethodCall $methodCall
     * @param \PHPStan\Analyser\Scope $scope
     */
    public function getTypeFromMethodCall($methodReflection, $methodCall, $scope) : \PHPStan\Type\Type
    {
        $defaultObjectType = new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Context\\AspectInterface');
        if (!($argument = $methodCall->args[0] ?? null) instanceof \PhpParser\Node\Arg) {
            return $defaultObjectType;
        }
        /** @var Arg $argument */
        if (!($string = $argument->value ?? null) instanceof \PhpParser\Node\Scalar\String_) {
            return $defaultObjectType;
        }
        switch ($string->value) {
            case 'date':
                return new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Context\\DateTimeAspect');
            case 'visibility':
                return new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Context\\VisibilityAspect');
            case 'frontend.user':
            case 'backend.user':
                return new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Context\\UserAspect');
            case 'workspace':
                return new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Context\\WorkspaceAspect');
            case 'language':
                return new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Context\\LanguageAspect');
            case 'typoscript':
                return new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Context\\TypoScriptAspect');
            default:
                return $defaultObjectType;
        }
    }
}
