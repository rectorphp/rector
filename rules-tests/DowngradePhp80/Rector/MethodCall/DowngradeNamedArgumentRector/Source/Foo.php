<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Source;

class Foo
{
    public function __construct(
        public ?array $a = null,
        public ?array $b = null
    ) {
    }
}