<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\Fixture;

final class UserVariable
{
    public function __construct(
        private string $usedDependency
    ) {
        echo $usedDependency;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\Fixture;

final class UserVariable
{
    public function __construct(
        string $usedDependency
    ) {
        echo $usedDependency;
    }
}

?>
