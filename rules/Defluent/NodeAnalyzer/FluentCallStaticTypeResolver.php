<?php

declare (strict_types=1);
namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\ReflectionProvider;
final class FluentCallStaticTypeResolver
{
    /**
     * @var \Rector\Defluent\NodeAnalyzer\ExprStringTypeResolver
     */
    private $exprStringTypeResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Defluent\NodeAnalyzer\ExprStringTypeResolver $exprStringTypeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->exprStringTypeResolver = $exprStringTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param MethodCall[] $chainMethodCalls
     * @return string[]
     */
    public function resolveCalleeUniqueTypes(array $chainMethodCalls) : array
    {
        $callerClassTypes = [];
        \end($chainMethodCalls);
        $lastMethodCallKey = \key($chainMethodCalls);
        $lastMethodCall = $chainMethodCalls[$lastMethodCallKey];
        $rootType = $this->exprStringTypeResolver->resolve($lastMethodCall->var);
        if ($rootType !== null) {
            $callerClassTypes[] = $rootType;
        }
        \reset($chainMethodCalls);
        // chain method calls are inversed
        $lastChainMethodCallKey = \key($chainMethodCalls);
        foreach ($chainMethodCalls as $key => $chainMethodCall) {
            $chainMethodCallType = $this->exprStringTypeResolver->resolve($chainMethodCall);
            if ($chainMethodCallType === null) {
                // last method call does not need a type
                if ($lastChainMethodCallKey === $key) {
                    continue;
                }
                return [];
            }
            $callerClassTypes[] = $chainMethodCallType;
        }
        $uniqueCallerClassTypes = \array_unique($callerClassTypes);
        return $this->filterOutAlreadyPresentParentClasses($uniqueCallerClassTypes);
    }
    /**
     * If a child class is with the parent class in the list, count them as 1
     *
     * @param class-string[] $types
     * @return class-string[]
     */
    private function filterOutAlreadyPresentParentClasses(array $types) : array
    {
        $secondTypes = $types;
        foreach ($types as $key => $type) {
            foreach ($secondTypes as $secondType) {
                if ($type === $secondType) {
                    continue;
                }
                if (!$this->reflectionProvider->hasClass($type)) {
                    continue;
                }
                $firstClassReflection = $this->reflectionProvider->getClass($type);
                if ($firstClassReflection->isSubclassOf($secondType)) {
                    unset($types[$key]);
                    continue 2;
                }
            }
        }
        return \array_values($types);
    }
}
