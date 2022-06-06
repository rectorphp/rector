<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\PHPStan\Type;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Type\DynamicMethodReturnTypeExtension;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
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
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : ?\RectorPrefix20220606\PHPStan\Type\Type
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
