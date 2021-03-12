<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Source;

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
