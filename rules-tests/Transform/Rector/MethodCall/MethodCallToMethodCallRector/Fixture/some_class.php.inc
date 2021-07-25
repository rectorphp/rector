<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;

class SomeClass
{
    public function __construct(
        private FirstDependency $firstDependency
    ) {
    }

    public function run()
    {
        $this->firstDependency->go();
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;

class SomeClass
{
    public function __construct(
        private FirstDependency $firstDependency, private \Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\SecondDependency $secondDependency
    ) {
    }

    public function run()
    {
        $this->secondDependency->away();
    }
}

?>
