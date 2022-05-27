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
    public function filterActionMethods(Class_ $class) : array
    {
        return \array_filter($class->getMethods(), function (ClassMethod $classMethod) {
            if ($classMethod->isMagic()) {
                return \false;
            }
            return $classMethod->isPublic();
        });
    }
}
