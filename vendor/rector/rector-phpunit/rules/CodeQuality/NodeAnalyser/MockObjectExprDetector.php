<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\CodeQuality\NodeFinder\VariableFinder;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\Reflection\ReflectionResolver;
final class MockObjectExprDetector
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private VariableFinder $variableFinder;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, VariableFinder $variableFinder, ReflectionResolver $reflectionResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableFinder = $variableFinder;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function hasMethodCallWithoutExpects(ClassMethod $classMethod): bool
    {
        /** @var array<Expr\MethodCall> $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, [MethodCall::class]);
        foreach ($methodCalls as $methodCall) {
            if (!$this->nodeNameResolver->isName($methodCall->name, 'method')) {
                continue;
            }
            if ($methodCall->var instanceof MethodCall) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    public function isUsedForMocking(Expr $expr, ClassMethod $classMethod): bool
    {
        if (!$expr instanceof Variable) {
            return \false;
        }
        $variableName = $this->nodeNameResolver->getName($expr);
        // to be safe
        if ($variableName === null) {
            return \true;
        }
        $relatedVariables = $this->variableFinder->find($classMethod, $variableName);
        // only self variable found, nothing to mock
        if (count($relatedVariables) === 1) {
            return \false;
        }
        // find out, how many are used in call likes as args
        /** @var array<Expr\MethodCall> $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, [MethodCall::class]);
        $mockObjectType = new ObjectType(PHPUnitClassName::MOCK_OBJECT);
        foreach ($methodCalls as $methodCall) {
            if (!$methodCall->var instanceof Variable) {
                continue;
            }
            if ($this->nodeNameResolver->isName($methodCall->var, $variableName)) {
                // variable is being called on, most like mocking, lets skip
                return \true;
            }
            if ($methodCall->isFirstClassCallable()) {
                continue;
            }
            // check if variable is passed as arg to a method that declares MockObject type parameter
            foreach ($methodCall->getArgs() as $argIndex => $arg) {
                if (!$arg->value instanceof Variable) {
                    continue;
                }
                if (!$this->nodeNameResolver->isName($arg->value, $variableName)) {
                    continue;
                }
                $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($methodCall);
                if (!$methodReflection instanceof MethodReflection) {
                    continue;
                }
                $variants = $methodReflection->getVariants();
                foreach ($variants as $variant) {
                    $parameters = $variant->getParameters();
                    foreach ($parameters as $parameter) {
                        $paramType = $parameter->getType();
                        if ($arg->name instanceof Identifier && $this->nodeNameResolver->isName($arg->name, $parameter->getName()) && $mockObjectType->isSuperTypeOf($paramType)->yes()) {
                            return \true;
                        }
                    }
                    if (isset($parameters[$argIndex])) {
                        $paramType = $parameters[$argIndex]->getType();
                        if ($mockObjectType->isSuperTypeOf($paramType)->yes()) {
                            return \true;
                        }
                    }
                }
            }
        }
        return \false;
    }
    public function isPropertyUsedForMocking(Class_ $class, string $propertyName): bool
    {
        // find out, how many are used in call likes as args
        /** @var array<Expr\MethodCall> $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstancesOfScoped($class->getMethods(), [MethodCall::class]);
        foreach ($methodCalls as $methodCall) {
            if (!$methodCall->var instanceof PropertyFetch) {
                continue;
            }
            $propertyFetch = $methodCall->var;
            if ($this->nodeNameResolver->isName($propertyFetch->name, $propertyName)) {
                // variable is being called on, most like mocking, lets skip
                return \true;
            }
        }
        return \false;
    }
}
