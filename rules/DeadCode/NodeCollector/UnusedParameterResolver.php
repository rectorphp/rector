<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeCollector;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeManipulator\ClassMethodManipulator;

final class UnusedParameterResolver
{
    public function __construct(
        private ClassMethodManipulator $classMethodManipulator
    ) {
    }

    /**
     * @return Param[]
     */
    public function resolve(ClassMethod $classMethod): array
    {
        $unusedParameters = [];

        foreach ($classMethod->params as $i => $param) {
            // skip property promotion
            /** @var Param $param */
            if ($param->flags !== 0) {
                continue;
            }

            if ($this->classMethodManipulator->isParameterUsedInClassMethod($param, $classMethod)) {
                continue;
            }

            $unusedParameters[$i] = $param;
        }

        return $unusedParameters;
    }
}
