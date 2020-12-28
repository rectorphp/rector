<?php

namespace Rector\TestPackageName\Tests\Rector\Arg\TestRector\Fixture;

class SomeClass
{
    public function run()
    {
        $this->something();
    }
}

?>
-----
<?php

namespace Rector\TestPackageName\Tests\Rector\Arg\TestRector\Fixture;

class SomeClass
{
    public function run()
    {
        $this->somethingElse();
    }
}

?>
