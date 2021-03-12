<?php

namespace Rector\CodeQuality\Tests\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class VendorPhar
{
    public function run()
    {
        require 'vendor/autoload.php';

        include '/vendor/autoload.php';

        include_once 'phar://vendor/autoload.php';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

class VendorPhar
{
    public function run()
    {
        require __DIR__ . '/vendor/autoload.php';

        include __DIR__ . '/vendor/autoload.php';

        include_once 'phar://vendor/autoload.php';
    }
}

?>
