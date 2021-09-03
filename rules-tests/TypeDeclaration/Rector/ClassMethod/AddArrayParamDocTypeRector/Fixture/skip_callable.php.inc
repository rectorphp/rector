<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

final class SkipCallable
{
    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }
}
