<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

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

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

function have_some_fun()
{
    $possibleDirectories = [];
    $directories = array_filter($possibleDirectories, 'file_exists');

    return $directories;
}

?>
