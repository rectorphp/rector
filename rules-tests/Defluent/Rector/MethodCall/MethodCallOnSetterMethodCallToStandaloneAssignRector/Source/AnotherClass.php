<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Source;

final class AnotherClass
{
    public function someFunction(): AnotherClass
    {
        return $this;
    }

    public function anotherFunction(): AnotherClass
    {
        return $this;
    }
}
