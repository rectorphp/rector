<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Source\Foo;

final class SomeStaticCall extends Foo
{
    public function __construct(string $name = null, array $attributes = [])
    {
        parent::__construct(a: [[$name ?? 0 => $attributes]]);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Source\Foo;

final class SomeStaticCall extends Foo
{
    public function __construct(string $name = null, array $attributes = [])
    {
        parent::__construct([[$name ?? 0 => $attributes]]);
    }
}

?>
