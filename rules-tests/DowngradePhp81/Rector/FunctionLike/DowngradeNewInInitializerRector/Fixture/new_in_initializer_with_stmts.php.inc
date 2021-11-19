<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

class SomeClass
{
    public function __construct(public stdClass $obj = new stdClass)
    {
        $obj->foo = 'bar';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

class SomeClass
{
    public function __construct(public ?stdClass $obj = null)
    {
        $this->obj = $obj ?? new stdClass;
        $obj->foo = 'bar';
    }
}

?>
