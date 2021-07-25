<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\SameTypeParentDependencyDifferentName;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\WithParentInjectAnnotationSecondDependency;

final class WithParentInjectDependencySameTypeDifferentName extends SameTypeParentDependencyDifferentName
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
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\SameTypeParentDependencyDifferentName;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\WithParentInjectAnnotationSecondDependency;

final class WithParentInjectDependencySameTypeDifferentName extends SameTypeParentDependencyDifferentName
{
    public function __construct(
        private FirstDependency $firstDependency
    ) {
    }

    public function run()
    {
        $this->wooohoooo->away();
    }
}

?>
