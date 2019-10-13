<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Include_\RemoveAutoloadingIncludeRector\Fixture;

final class SimpleInclude
{
    public function run()
    {
        include 'SomeFile.php';
        include_once 'AnoterSomeFile.php';
        require 'SomeFile.php';
        require_once 'SomeFile.php';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Include_\RemoveAutoloadingIncludeRector\Fixture;

final class SimpleInclude
{
    public function run()
    {
    }
}

?>
