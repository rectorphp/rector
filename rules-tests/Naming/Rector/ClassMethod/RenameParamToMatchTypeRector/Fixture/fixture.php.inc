<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

class SomeClass
{
    /**
     * @var EliteManager
     */
    private $eventManager;

    public function __construct(EliteManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

class SomeClass
{
    /**
     * @var EliteManager
     */
    private $eventManager;

    public function __construct(EliteManager $eliteManager)
    {
        $this->eventManager = $eliteManager;
    }
}

?>
