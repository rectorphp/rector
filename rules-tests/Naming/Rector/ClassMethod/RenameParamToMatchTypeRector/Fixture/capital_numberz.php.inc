<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\PHP9Crawler;

class CapitalNumberz
{
    public function __construct(PHP9Crawler $some)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\PHP9Crawler;

class CapitalNumberz
{
    public function __construct(PHP9Crawler $php9Crawler)
    {
    }
}

?>
