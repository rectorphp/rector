<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;

final class ChainCallsStaticTypeResolver
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
    public function resolveCalleeUniqueTypes(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): array
    {
        $rootClassType = $this->exprStringTypeResolver->resolve($assignAndRootExpr->getRootExpr());
        if ($rootClassType === null) {
            return [];
        }

        $callerClassTypes = [];
        $callerClassTypes[] = $rootClassType;

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
