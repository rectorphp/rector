<?php

namespace Rector\Tests\CodingStyle\Rector\Include_\FollowRequireByDirRector\Fixture;

class Phar
{
    public function run()
    {
        require 'vendor/autoload.php';
        require '/vendor/autoload.php';
        require 'phar://vendor/autoload.php';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Include_\FollowRequireByDirRector\Fixture;

class Phar
{
    public function run()
    {
        require __DIR__ . '/vendor/autoload.php';
        require __DIR__ . '/vendor/autoload.php';
        require 'phar://vendor/autoload.php';
    }
}

?>
