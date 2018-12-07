<?php

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class WithSomeInMethodPropertySetDefaultAbove
{
    private $property;

    public function run()
    {
        $this->property = '';
        $this->property[] = 1;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class WithSomeInMethodPropertySetDefaultAbove
{
    private $property;

    public function run()
    {
        $this->property = [];
        $this->property[] = 1;
    }
}

?>
