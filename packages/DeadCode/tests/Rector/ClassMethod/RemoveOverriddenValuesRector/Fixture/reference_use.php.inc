<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

final class ReferenceUse
{
    public function run(&$directories)
    {
        $directories = 1;
        $directories = 4;
        $directories = 5;
    }

    public function plusRun(&$directories)
    {
        $directories = 1;
        $directories += 4;
    }

    public function get(&$directories)
    {
        $directories = 1;
        $directories = 4;
        return $directories;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

final class ReferenceUse
{
    public function run(&$directories)
    {
        $directories = 5;
    }

    public function plusRun(&$directories)
    {
        $directories = 1;
        $directories += 4;
    }

    public function get(&$directories)
    {
        $directories = 4;
        return $directories;
    }
}

?>
