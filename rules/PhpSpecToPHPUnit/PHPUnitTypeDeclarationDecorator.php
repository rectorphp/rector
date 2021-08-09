<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;

/**
 * Decorate setUp() and tearDown() with "void" when local TestClass class uses them
 */
final class PHPUnitTypeDeclarationDecorator
{
    public function __construct(
        private AstResolver $astResolver
    ) {
    }

    public function decorate(ClassMethod $classMethod): void
    {
        // skip test run
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }

        $setUpClassMethod = $this->astResolver->resolveClassMethod('PHPUnit\Framework\TestCase', MethodName::SET_UP);
        if (! $setUpClassMethod instanceof ClassMethod) {
            return;
        }

        $classMethod->returnType = $setUpClassMethod->returnType;
    }
}
