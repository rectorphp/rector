<?php

namespace Rector\CodeQuality\Tests\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class Dots
{
    public function run()
    {
        require './vendor/autoload.php';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class Dots
{
    public function run()
    {
        require __DIR__ . './vendor/autoload.php';
    }
}

?>
