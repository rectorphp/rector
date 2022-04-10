<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

final class FullList
{
    public function run()
    {
        require 'autoload.php';

        require "autoload.php";
        require_once 'autoload.php';
        include 'autoload.php';
        include_once 'autoload.php';

        require $variable;
        require __DIR__ . $variable;
        require __DIR__ . 'string';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

final class FullList
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require __DIR__ . "/autoload.php";
        require_once __DIR__ . '/autoload.php';
        include __DIR__ . '/autoload.php';
        include_once __DIR__ . '/autoload.php';

        require $variable;
        require __DIR__ . $variable;
        require __DIR__ . 'string';
    }
}

?>
