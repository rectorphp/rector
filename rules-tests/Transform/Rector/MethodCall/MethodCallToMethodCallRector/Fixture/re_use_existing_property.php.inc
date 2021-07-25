<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\SecondDependency;

final class ReUseExistingProperty
{
    public function __construct(
        private FirstDependency $firstDependency,
        private SecondDependency $secondDependency
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
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\SecondDependency;

final class ReUseExistingProperty
{
    public function __construct(
        private FirstDependency $firstDependency,
        private SecondDependency $secondDependency
    ) {
    }

    public function run()
    {
        $this->secondDependency->away();
    }
}

?>
