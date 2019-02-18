<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

final class SomeController
{
    public function run()
    {
        $directories = [];
        $possibleDirectories = [];
        $directories = array_filter($possibleDirectories, 'file_exists');

        return $directories;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

final class SomeController
{
    public function run()
    {
        $possibleDirectories = [];
        $directories = array_filter($possibleDirectories, 'file_exists');
        return $directories;
    }
}

?>
