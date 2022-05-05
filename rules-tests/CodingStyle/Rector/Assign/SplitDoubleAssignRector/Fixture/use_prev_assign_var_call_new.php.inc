<?php

namespace Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\Fixture;

class UsePrevAssignVarCallNew
{
    public function run()
    {
        $one = $two = $this->execute();
        $anotherOne = $anotherTwo = new \stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\Fixture;

class UsePrevAssignVarCallNew
{
    public function run()
    {
        $one = $this->execute();
        $two = $one;
        $anotherOne = new \stdClass;
        $anotherTwo = $anotherOne;
    }
}

?>
