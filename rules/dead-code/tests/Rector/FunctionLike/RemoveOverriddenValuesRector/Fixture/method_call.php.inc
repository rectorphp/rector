<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class MethodCall
{
    public function run()
    {
        $directories = 1;
        $anotherDirectories = 1;
        $directories = 2;
        $this->store($directories);
        $anotherDirectories = 2;
        $directories = 3;
        $anotherDirectories = 3;
        $directories = 4;
        $directories = 5;
        return $directories + $anotherDirectories;
    }

    public function store(int $directories)
    {
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class MethodCall
{
    public function run()
    {
        $directories = 2;
        $this->store($directories);
        $anotherDirectories = 3;
        $directories = 5;
        return $directories + $anotherDirectories;
    }

    public function store(int $directories)
    {
    }
}

?>
