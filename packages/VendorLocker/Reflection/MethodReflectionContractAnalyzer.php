<?php

declare (strict_types=1);
namespace Rector\VendorLocker\Reflection;

use PHPStan\Reflection\ClassReflection;
final class MethodReflectionContractAnalyzer
{
    public function hasInterfaceContract(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
}
