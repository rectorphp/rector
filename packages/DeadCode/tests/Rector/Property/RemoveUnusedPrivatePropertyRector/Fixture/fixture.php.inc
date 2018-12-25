<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SomeClass
{
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SomeClass
{
    public $publicProperty;
    protected $protectedProperty;
}

?>
