<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use ReflectionMethod;

/**
 * Decorate setUp() and tearDown() with "void" when local TestClass class uses them
 */
final class PHPUnitTypeDeclarationDecorator
{
    public function decorate(ClassMethod $classMethod): void
    {
        if (! class_exists('PHPUnit\Framework\TestCase')) {
            return;
        }

        // skip test run
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }

        $reflectionMethod = new ReflectionMethod('PHPUnit\Framework\TestCase', MethodName::SET_UP);
        if (! $reflectionMethod->hasReturnType()) {
            return;
        }

        $returnType = (string) $reflectionMethod->getReturnType();
        $classMethod->returnType = new Identifier($returnType);
    }
}
