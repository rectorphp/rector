<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class Fixture3
{
    public $someString = '';

    public $anotherString = '';

    public function run()
    {
        $this->someString[] = 1;

        $this->anotherString[1] = 1;
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class Fixture3
{
    public $someString = [];

    public $anotherString = '';

    public function run()
    {
        $this->someString[] = 1;

        $this->anotherString[1] = 1;
    }
}

?>
