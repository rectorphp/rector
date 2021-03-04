<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeCollector;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;

final class UnusedParameterResolver
{
    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        ClassMethodManipulator $classMethodManipulator,
        NodeNameResolver $nodeNameResolver,
        NodeComparator $nodeComparator
    ) {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
    }

    /**
     * @param Class_[] $childrenOfClass
     * @return Param[]
     */
    public function resolve(ClassMethod $classMethod, string $methodName, array $childrenOfClass): array
    {
        $unusedParameters = $this->resolveUnusedParameters($classMethod);
        if ($unusedParameters === []) {
            return [];
        }

        foreach ($childrenOfClass as $childOfClass) {
            $methodOfChild = $childOfClass->getMethod($methodName);
            if (! $methodOfChild instanceof ClassMethod) {
                continue;
            }

            $unusedParameters = $this->getParameterOverlap(
                $unusedParameters,
                $this->resolveUnusedParameters($methodOfChild)
            );
        }

        return $unusedParameters;
    }

    /**
     * @return Param[]
     */
    private function resolveUnusedParameters(ClassMethod $classMethod): array
    {
        $unusedParameters = [];

        foreach ($classMethod->params as $i => $param) {
            // skip property promotion
            /** @var Param $param */
            if ($param->flags !== 0) {
                continue;
            }

            if ($this->classMethodManipulator->isParameterUsedInClassMethod($param, $classMethod)) {
                // reset to keep order of removed arguments, if not construtctor - probably autowired
                if (! $this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
                    $unusedParameters = [];
                }

                continue;
            }

            $unusedParameters[$i] = $param;
        }

        return $unusedParameters;
    }

    /**
     * @param Param[] $parameters1
     * @param Param[] $parameters2
     * @return Param[]
     */
    private function getParameterOverlap(array $parameters1, array $parameters2): array
    {
        return array_uintersect(
            $parameters1,
            $parameters2,
            function (Param $firstParam, Param $secondParam): int {
                return $this->nodeComparator->areNodesEqual($firstParam, $secondParam) ? 0 : 1;
            }
        );
    }
}
