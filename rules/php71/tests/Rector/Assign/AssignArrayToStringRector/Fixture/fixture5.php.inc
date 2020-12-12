<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

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

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

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
