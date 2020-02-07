<?php

namespace Rector\CodeQuality\Tests\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class SomeClass
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

namespace Rector\CodeQuality\Tests\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require $variable;
    }
}

?>
