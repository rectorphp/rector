<?php

namespace Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\Fixture;

class UsePrevAssignVarCallNew
{
    public function run()
    {
        $one = $two = $this->execute();
        $one = $two = self::execute();
        $one = $two = execute();
        $one = $two = new \stdClass;
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
        $one = self::execute();
        $two = $one;
        $one = execute();
        $two = $one;
        $one = new \stdClass;
        $two = $one;
    }
}

?>
