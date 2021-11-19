<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

class SomeClass
{
    public stdClass $obj;

    public function __construct(stdClass $obj = new stdClass)
    {
        $this->obj = $obj;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

class SomeClass
{
    public stdClass $obj;

    public function __construct(stdClass $obj = null)
    {
        $obj ??= new stdClass;
        $this->obj = $obj;
    }
}

?>
