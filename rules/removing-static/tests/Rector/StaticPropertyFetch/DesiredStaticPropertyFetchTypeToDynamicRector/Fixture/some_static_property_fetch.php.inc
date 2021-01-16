<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Source\SomeStaticType;

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

namespace Rector\RemovingStatic\Tests\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Source\SomeStaticType;

final class SomeStaticPropertyFetch
{
    private \Rector\RemovingStatic\Tests\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Source\SomeStaticType $someStaticType;
    public function __construct(\Rector\RemovingStatic\Tests\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Source\SomeStaticType $someStaticType)
    {
        $this->someStaticType = $someStaticType;
    }
    public function run()
    {
        return $this->someStaticType->someProperty;
    }
}

?>
