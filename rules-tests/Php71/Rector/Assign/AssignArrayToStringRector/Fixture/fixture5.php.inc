<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

class Fixture5
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

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

class Fixture5
{
    private $property;

    public function run()
    {
        $this->property = [];
        $this->property[] = 1;
    }
}

?>
