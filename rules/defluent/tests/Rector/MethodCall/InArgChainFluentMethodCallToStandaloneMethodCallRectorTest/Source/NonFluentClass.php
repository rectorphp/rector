<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source;

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
