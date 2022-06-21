<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\EliteManager as AwesomeManager;

class UseAliased
{
    public function __construct(private AwesomeManager $eventManager)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\EliteManager as AwesomeManager;

class UseAliased
{
    public function __construct(private AwesomeManager $awesomeManager)
    {
    }
}

?>
