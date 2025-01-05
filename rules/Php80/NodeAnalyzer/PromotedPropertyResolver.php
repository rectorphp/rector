<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\ValueObject\MethodName;
final class PromotedPropertyResolver
{
    /**
     * @return Param[]
     */
    public function resolveFromClass(Class_ $class) : array
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return [];
        }
        $promotedPropertyParams = [];
        foreach ($constructClassMethod->getParams() as $param) {
            if (!$param->isPromoted()) {
                continue;
            }
            $promotedPropertyParams[] = $param;
        }
        return $promotedPropertyParams;
    }
}
