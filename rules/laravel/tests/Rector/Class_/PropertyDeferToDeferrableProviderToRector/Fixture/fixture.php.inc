<?php

namespace Rector\Laravel\Tests\Rector\Class_\PropertyDeferToDeferrableProviderToRector\Fixture;

use Illuminate\Support\ServiceProvider;

final class SomeServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\Class_\PropertyDeferToDeferrableProviderToRector\Fixture;

use Illuminate\Support\ServiceProvider;

final class SomeServiceProvider extends ServiceProvider implements \Illuminate\Contracts\Support\DeferrableProvider
{
}

?>
