<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Source\InstantiableViaNamedConstructor;

final class SkipStaticCallNullCoalesceOperator
{
    public function __construct(private ?InstantiableViaNamedConstructor $foo = null)
    {
        $this->foo ??= InstantiableViaNamedConstructor::make(100);
    }
}
