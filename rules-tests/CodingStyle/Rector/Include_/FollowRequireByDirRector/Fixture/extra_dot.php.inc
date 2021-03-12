<?php

namespace Rector\Tests\CodingStyle\Rector\Include_\FollowRequireByDirRector\Fixture;

class ExtraDot
{
    public function run()
    {
        require './inc/file.php';
        require "./inc/file.php";
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Include_\FollowRequireByDirRector\Fixture;

class ExtraDot
{
    public function run()
    {
        require __DIR__ . '/inc/file.php';
        require __DIR__ . "/inc/file.php";
    }
}

?>
