<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class OnNew
{
    public function __construct(string $name)
    {
    }

    public function run()
    {
        new OnNew(name: 'test');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class OnNew
{
    public function __construct(string $name)
    {
    }

    public function run()
    {
        new OnNew('test');
    }
}

?>
