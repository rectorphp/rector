<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class Dots
{
    public function run()
    {
        require './vendor/autoload.php';

        require '../AbsolutizeRequireAndIncludePathRectorTest.php';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class Dots
{
    public function run()
    {
        require __DIR__ . '/vendor/autoload.php';

        require __DIR__ . '/../AbsolutizeRequireAndIncludePathRectorTest.php';
    }
}

?>
