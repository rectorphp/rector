<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;

final class FluentCallStaticTypeResolver
{
    /**
     * @var ExprStringTypeResolver
     */
    private $exprStringTypeResolver;

    public function __construct(ExprStringTypeResolver $exprStringTypeResolver)
    {
        $this->exprStringTypeResolver = $exprStringTypeResolver;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return string[]
     */
    public function resolveCalleeUniqueTypes(array $chainMethodCalls): array
    {
        $callerClassTypes = [];

        $lastMethodCallKey = array_key_last($chainMethodCalls);
        $lastMethodCall = $chainMethodCalls[$lastMethodCallKey];

        $rootType = $this->exprStringTypeResolver->resolve($lastMethodCall->var);
        if ($rootType !== null) {
            $callerClassTypes[] = $rootType;
        }

        // chain method calls are inversed
        $lastChainMethodCallKey = array_key_first($chainMethodCalls);

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

        $uniqueCallerClassTypes = array_unique($callerClassTypes);
        return $this->filterOutAlreadyPresentParentClasses($uniqueCallerClassTypes);
    }

    /**
     * If a child class is with the parent class in the list, count them as 1
     *
     * @param string[] $types
     * @return string[]
     */
    private function filterOutAlreadyPresentParentClasses(array $types): array
    {
        $secondTypes = $types;

        foreach ($types as $key => $type) {
            foreach ($secondTypes as $secondType) {
                if ($type === $secondType) {
                    continue;
                }

                if (is_a($type, $secondType, true)) {
                    unset($types[$key]);
                    continue 2;
                }
            }
        }

        return array_values($types);
    }
}
