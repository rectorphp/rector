<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Source\InstantiableViaNamedConstructor;

final class SkipStaticCallRightCoalesce
{
    private InstantiableViaNamedConstructor $foo;

    public function __construct(?InstantiableViaNamedConstructor $foo = null)
    {
        $this->foo = $foo ?? InstantiableViaNamedConstructor::make(100);
    }

}
