<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class OnNew2
{
    public function __construct(string $name)
    {
    }

    public function run()
    {
        new self(name: 'test');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class OnNew2
{
    public function __construct(string $name)
    {
    }

    public function run()
    {
        new self('test');
    }
}

?>
