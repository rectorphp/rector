<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Source;

final class DummyUserProvider
{
    public function getDummyUser()
    {
        return new DummyUser();
    }
}
