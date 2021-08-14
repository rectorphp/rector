<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Source;

class ValueObject
{
    public $class = FluentClass::class;

    public const A_CLASS = FluentClass::class;
}
