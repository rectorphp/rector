<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class Foo
{
    public function doFoo()
    {
        new self(
            'foo',
            'bar',
        );
    }
}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class Foo
{
    public function doFoo()
    {
        new self('foo', 'bar');
    }
}
?>
