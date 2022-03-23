<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
final class SymfonyControllerFilter
{
    /**
     * @return ClassMethod[]
     */
    public function filterActionMethods(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        return \array_filter($class->getMethods(), function (\PhpParser\Node\Stmt\ClassMethod $classMethod) {
            if ($classMethod->isMagic()) {
                return \false;
            }
            return $classMethod->isPublic();
        });
    }
}
