<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

function have_some_fun()
{
    $directories = [];
    $possibleDirectories = [];
    $directories = array_filter($possibleDirectories, 'file_exists');

    return $directories;
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

function have_some_fun()
{
    $possibleDirectories = [];
    $directories = array_filter($possibleDirectories, 'file_exists');
    return $directories;
}

?>
