<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class MultipleAssigns
{
    public function run()
    {
        $directories = 1;
        $anotherDirectories = 1;
        $directories = 2;
        $anotherDirectories = 2;
        $directories = 3;
        $anotherDirectories = 3;
        $directories = 4;
        $directories = 5;
        return $directories + $anotherDirectories;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class MultipleAssigns
{
    public function run()
    {
        $anotherDirectories = 3;
        $directories = 5;
        return $directories + $anotherDirectories;
    }
}

?>
