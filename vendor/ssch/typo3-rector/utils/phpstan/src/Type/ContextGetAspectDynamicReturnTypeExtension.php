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
final class ContextGetAspectDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return 'TYPO3\\CMS\\Core\\Context\\Context';
    }
    public function isMethodSupported(MethodReflection $methodReflection) : bool
    {
        return 'getAspect' === $methodReflection->getName();
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : ?\PHPStan\Type\Type
    {
        $defaultObjectType = new ObjectType('TYPO3\\CMS\\Core\\Context\\AspectInterface');
        if (null === $methodCall->args) {
            return $defaultObjectType;
        }
        $firstArg = $methodCall->args[0];
        if (!$firstArg instanceof Arg) {
            return $defaultObjectType;
        }
        $argValue = $firstArg->value;
        if (!$argValue instanceof String_) {
            return $defaultObjectType;
        }
        switch ($argValue->value) {
            case 'date':
                return new ObjectType('TYPO3\\CMS\\Core\\Context\\DateTimeAspect');
            case 'visibility':
                return new ObjectType('TYPO3\\CMS\\Core\\Context\\VisibilityAspect');
            case 'frontend.user':
            case 'backend.user':
                return new ObjectType('TYPO3\\CMS\\Core\\Context\\UserAspect');
            case 'workspace':
                return new ObjectType('TYPO3\\CMS\\Core\\Context\\WorkspaceAspect');
            case 'language':
                return new ObjectType('TYPO3\\CMS\\Core\\Context\\LanguageAspect');
            case 'typoscript':
                return new ObjectType('TYPO3\\CMS\\Core\\Context\\TypoScriptAspect');
            default:
                return $defaultObjectType;
        }
    }
}
