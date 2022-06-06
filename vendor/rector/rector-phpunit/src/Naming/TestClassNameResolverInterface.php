<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Naming;

interface TestClassNameResolverInterface
{
    /**
     * @return string[]
     */
    public function resolve(string $className) : array;
}
