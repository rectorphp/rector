<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class Fixture
{
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class Fixture
{
    public $publicProperty;
    protected $protectedProperty;
}

?>
