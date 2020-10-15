<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source;

final class DummyUserProvider
{
    public function getDummyUser()
    {
        return new DummyUser();
    }
}
