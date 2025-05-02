<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPUnit\CodeQuality\Enum\NonAssertNonStaticMethods;
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
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function detectTestCaseCall($call) : bool
    {
        $objectCaller = $call instanceof MethodCall ? $call->var : $call->class;
        if (!$this->nodeTypeResolver->isObjectType($objectCaller, new ObjectType('PHPUnit\\Framework\\TestCase'))) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($call->name);
        if (\strncmp((string) $methodName, 'assert', \strlen('assert')) !== 0 && !\in_array($methodName, NonAssertNonStaticMethods::ALL, \true)) {
            return \false;
        }
        if ($call instanceof StaticCall && !$this->nodeNameResolver->isNames($call->class, ['static', 'self'])) {
            return \false;
        }
        $extendedMethodReflection = $this->resolveMethodReflection($call);
        if (!$extendedMethodReflection instanceof ExtendedMethodReflection) {
            return \false;
        }
        // only handle methods in TestCase or Assert class classes
        $declaringClassName = $extendedMethodReflection->getDeclaringClass()->getName();
        return \in_array($declaringClassName, [PHPUnitClassName::TEST_CASE, PHPUnitClassName::ASSERT]);
    }
    public function detectTestCaseCallForStatic(MethodCall $methodCall) : bool
    {
        if (!$this->detectTestCaseCall($methodCall)) {
            return \false;
        }
        $extendedMethodReflection = $this->resolveMethodReflection($methodCall);
        return $extendedMethodReflection instanceof ExtendedMethodReflection && $extendedMethodReflection->isStatic();
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function resolveMethodReflection($call) : ?ExtendedMethodReflection
    {
        $methodName = $this->nodeNameResolver->getName($call->name);
        $classReflection = $this->reflectionResolver->resolveClassReflection($call);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        return $classReflection->getNativeMethod($methodName);
    }
}
