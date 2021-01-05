<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class DoNotRemoveMethodParameterUsedInArg
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

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class DoNotRemoveMethodParameterUsedInArg
{
    public function __construct(B $b)
    {
        new C($b);
    }
}

?>
