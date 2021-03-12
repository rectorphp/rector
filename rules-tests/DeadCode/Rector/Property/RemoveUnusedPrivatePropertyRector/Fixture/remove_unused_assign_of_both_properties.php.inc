<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class RemoveUnusedAssignOfBothProperties
{
    private $b;
    private $c;

    public function __construct(B $b)
    {
        $this->b = $b;
        $this->c = new C($b);
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class RemoveUnusedAssignOfBothProperties
{
    public function __construct(B $b)
    {
    }
}

?>
