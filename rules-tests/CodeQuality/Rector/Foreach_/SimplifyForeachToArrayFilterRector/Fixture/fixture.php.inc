<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Fixture;

$directories = [];
$possibleDirectories = [];
foreach ($possibleDirectories as $possibleDirectory) {
    if (file_exists($possibleDirectory)) {
        $directories[] = $possibleDirectory;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Fixture;

$directories = [];
$possibleDirectories = [];
$directories = array_filter($possibleDirectories, 'file_exists');

?>
