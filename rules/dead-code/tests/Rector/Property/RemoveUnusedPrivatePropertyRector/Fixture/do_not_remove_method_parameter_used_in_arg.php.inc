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

    public function get()
    {
        return $this->c;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class DoNotRemoveMethodParameterUsedInArg
{
    private $c;

    public function __construct(B $b)
    {
        $this->c = new C($b);
    }

    public function get()
    {
        return $this->c;
    }
}

?>
