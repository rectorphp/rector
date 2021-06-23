<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class OnNew3
{
    public function __construct(string $name)
    {
    }

    public function run()
    {
        new static(name: 'test');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class OnNew3
{
    public function __construct(string $name)
    {
    }

    public function run()
    {
        new static('test');
    }
}

?>
