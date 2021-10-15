<?php

namespace Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

final class ReplaceWithConstant
{
    public function run()
    {
        $this->call('asc');
        $this->call('Asc');
        $this->call('DESC');
    }

    public function call(string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

final class ReplaceWithConstant
{
    public function run()
    {
        $this->call(\Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Source\Order::ASC);
        $this->call(\Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Source\Order::ASC);
        $this->call(\Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Source\Order::DESC);
    }

    public function call(string $value)
    {
    }
}

?>
