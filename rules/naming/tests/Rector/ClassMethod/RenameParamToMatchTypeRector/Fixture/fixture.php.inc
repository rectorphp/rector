<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

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

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

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
