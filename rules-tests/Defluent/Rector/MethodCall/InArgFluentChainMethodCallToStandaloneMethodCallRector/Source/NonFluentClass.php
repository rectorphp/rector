<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Source;

final class NonFluentClass
{
    public function number()
    {
        return 5;
    }

    public function letter()
    {
        return 'Z';
    }
}
