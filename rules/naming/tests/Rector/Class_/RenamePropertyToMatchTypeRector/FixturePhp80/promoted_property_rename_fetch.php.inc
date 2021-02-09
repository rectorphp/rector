<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

final class PromotedPropertyRenameFetch
{
    public function __construct(
        private EliteManager $eventManager
    )
    {
    }

    public function run()
    {
        return $this->eventManager;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

final class PromotedPropertyRenameFetch
{
    public function __construct(
        private EliteManager $eliteManager
    )
    {
    }

    public function run()
    {
        return $this->eliteManager;
    }
}

?>
