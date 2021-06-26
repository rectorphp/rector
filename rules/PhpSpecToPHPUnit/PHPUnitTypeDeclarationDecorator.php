<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function decorate(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        // skip test run
        if (\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }
        $reflectionMethod = $this->reflectionResolver->resolveNativeClassMethodReflection('PHPUnit\\Framework\\TestCase', \Rector\Core\ValueObject\MethodName::SET_UP);
        if (!$reflectionMethod instanceof \ReflectionMethod) {
            return;
        }
        if (!$reflectionMethod->hasReturnType()) {
            return;
        }
        $returnType = $reflectionMethod->getReturnType();
        $returnTypeName = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : (string) $returnType;
        $classMethod->returnType = new \PhpParser\Node\Identifier($returnTypeName);
    }
}
