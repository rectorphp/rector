<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Decorate setUp() and tearDown() with "void" when local TestClass class uses them
 */
final class PHPUnitTypeDeclarationDecorator
{
    public function __construct(
        private ReflectionResolver $reflectionResolver
    ) {
    }

    public function decorate(ClassMethod $classMethod): void
    {
        // skip test run
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }

        $reflectionMethod = $this->reflectionResolver->resolveNativeClassMethodReflection(
            'PHPUnit\Framework\TestCase',
            MethodName::SET_UP
        );

        if (! $reflectionMethod instanceof ReflectionMethod) {
            return;
        }

        if (! $reflectionMethod->hasReturnType()) {
            return;
        }

        $returnType = $reflectionMethod->getReturnType();
        $returnTypeName = $returnType instanceof ReflectionNamedType
            ? $returnType->getName()
            : (string) $returnType;

        $classMethod->returnType = new Identifier($returnTypeName);
    }
}
