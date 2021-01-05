<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class DoNotRemoveMethodParameterUsed
{
    private B $b;
    private C $c;

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

class DoNotRemoveMethodParameterUsed
{
    public function __construct(B $b)
    {
        new C($b);
    }
}

?>
