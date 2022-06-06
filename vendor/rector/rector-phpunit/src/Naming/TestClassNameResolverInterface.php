<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPUnit\Naming;

interface TestClassNameResolverInterface
{
    /**
     * @return string[]
     */
    public function resolve(string $className) : array;
}
