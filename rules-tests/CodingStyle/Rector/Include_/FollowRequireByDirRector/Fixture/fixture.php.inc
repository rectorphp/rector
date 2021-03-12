<?php

namespace Rector\CodingStyle\Tests\Rector\Include_\FollowRequireByDirRector\Fixture;

class Fixture
{
    public function run()
    {
        require 'autoload.php';

        require "autoload.php";
        require_once 'autoload.php';
        include 'autoload.php';
        include_once 'autoload.php';
        require 'path/' . $variable;
        require $variable;
        require __DIR__ . $variable;
        require __DIR__ . 'string';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Include_\FollowRequireByDirRector\Fixture;

class Fixture
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require __DIR__ . "/autoload.php";
        require_once __DIR__ . '/autoload.php';
        include __DIR__ . '/autoload.php';
        include_once __DIR__ . '/autoload.php';
        require __DIR__ . '/path/' . $variable;
        require $variable;
        require __DIR__ . $variable;
        require __DIR__ . 'string';
    }
}

?>
