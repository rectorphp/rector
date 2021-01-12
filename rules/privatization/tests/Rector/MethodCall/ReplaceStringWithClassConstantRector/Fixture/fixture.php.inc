<?php

namespace Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

class SomeClass
{
    public function run()
    {
        $this->call('name');
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

class SomeClass
{
    public function run()
    {
        $this->call(Placeholder::NAME);
    }
}

?>
