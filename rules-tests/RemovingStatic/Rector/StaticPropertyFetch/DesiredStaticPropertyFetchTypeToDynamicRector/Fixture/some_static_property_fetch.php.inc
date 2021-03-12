<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Source\SomeStaticType;

final class SomeStaticPropertyFetch
{
    public function run()
    {
        return SomeStaticType::$someProperty;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Source\SomeStaticType;

final class SomeStaticPropertyFetch
{
    public function __construct(private \Rector\Tests\RemovingStatic\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Source\SomeStaticType $someStaticType)
    {
    }
    public function run()
    {
        return $this->someStaticType->someProperty;
    }
}

?>
