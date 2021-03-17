<?php

namespace Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

final class ReplaceWithConstant
{
    public function run()
    {
        $this->call('name');
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
        $this->call(\Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Source\Placeholder::NAME);
    }

    public function call(string $value)
    {
    }
}

?>
