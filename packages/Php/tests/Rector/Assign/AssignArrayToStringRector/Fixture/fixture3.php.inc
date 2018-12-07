<?php

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class WithSomeStaticString2
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

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class WithSomeStaticString2
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
