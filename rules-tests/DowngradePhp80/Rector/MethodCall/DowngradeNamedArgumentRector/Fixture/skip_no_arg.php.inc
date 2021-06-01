<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Source\Foo;

class SkipNoArg extends Foo
{
    public function __construct(string $name = null, array $attributes = [])
    {
        parent::__construct();
    }
}

?>
