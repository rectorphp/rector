<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class Fixture
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

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class Fixture
{
    public function run()
    {
        $possibleDirectories = [];
        $directories = array_filter($possibleDirectories, 'file_exists');

        return $directories;
    }
}

?>
