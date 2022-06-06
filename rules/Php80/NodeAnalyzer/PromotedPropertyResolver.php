<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
final class PromotedPropertyResolver
{
    /**
     * @return Param[]
     */
    public function resolveFromClass(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        $promotedPropertyParams = [];
        foreach ($constructClassMethod->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }
            $promotedPropertyParams[] = $param;
        }
        return $promotedPropertyParams;
    }
}
