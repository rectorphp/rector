<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

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

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class ExtraDot
{
    public function run()
    {
        require __DIR__ . '/inc/file.php';
        require __DIR__ . "/inc/file.php";
    }
}

?>
