<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Testing\PHPUnit\PHPUnitEnvironment;
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
        if (PHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }

        $reflectionMethod = new ReflectionMethod('PHPUnit\Framework\TestCase', 'setUp');
        if (! $reflectionMethod->hasReturnType()) {
            return;
        }

        $returnType = (string) $reflectionMethod->getReturnType();
        $classMethod->returnType = new Identifier($returnType);
    }
}
