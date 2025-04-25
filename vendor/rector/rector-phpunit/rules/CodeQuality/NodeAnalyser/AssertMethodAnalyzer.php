<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPUnit\CodeQuality\Enum\NonAssertStaticableMethods;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\Reflection\ReflectionResolver;
final class AssertMethodAnalyzer
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function detectTestCaseCall($call) : bool
    {
        $methodName = $this->nodeNameResolver->getName($call->name);
        if (\strncmp((string) $methodName, 'assert', \strlen('assert')) !== 0 && !\in_array($methodName, NonAssertStaticableMethods::ALL)) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($call);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if ($call instanceof StaticCall && !$this->nodeNameResolver->isNames($call->class, ['static', 'self'])) {
            return \false;
        }
        $extendedMethodReflection = $classReflection->getNativeMethod($methodName);
        // only handle methods in TestCase or Assert class classes
        $declaringClassName = $extendedMethodReflection->getDeclaringClass()->getName();
        return \in_array($declaringClassName, [PHPUnitClassName::TEST_CASE, PHPUnitClassName::ASSERT]);
    }
}
