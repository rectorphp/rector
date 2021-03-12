<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class Fixture
{
    public function run()
    {
        require 'autoload.php';

        require $variable;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class Fixture
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require $variable;
    }
}

?>
