<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\PHP9Crawler;

class CapitalNumberz
{
    public function __construct(PHP9Crawler $some)
    {
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\PHP9Crawler;

class CapitalNumberz
{
    public function __construct(PHP9Crawler $php9Crawler)
    {
    }
}

?>
