<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

class SomeClass
{
    public function __construct(public stdClass $o1 = new stdClass, public stdClass $o2 = new stdClass)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

class SomeClass
{
    public function __construct(public ?stdClass $o1 = null, public ?stdClass $o2 = null)
    {
        $this->o1 = $o1 ?? new stdClass;
        $this->o2 = $o2 ?? new stdClass;
    }
}

?>
