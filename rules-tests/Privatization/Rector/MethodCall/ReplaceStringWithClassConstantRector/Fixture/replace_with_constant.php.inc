<?php

namespace Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

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

namespace Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

final class ReplaceWithConstant
{
    public function run()
    {
        $this->call(\Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\Source\Placeholder::NAME);
    }

    public function call(string $value)
    {
    }
}

?>
