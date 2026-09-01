<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic;

use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\TestCase;
final class ClassTypeDetector
{
    public function isTestClass(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf(TestCase::class)) {
            return \true;
        }
        if ($classReflection->isSubclassOf('PHPUnit_Framework_TestCase')) {
            return \true;
        }
        return $classReflection->implementsInterface('RectorPrefix202609\Behat\Behat\Context\Context');
    }
}
