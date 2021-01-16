<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\EliteManager;

class RenameParam
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

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\EliteManager;

class RenameParam
{
    /**
     * @var EliteManager
     */
    private $eliteManager;

    public function __construct(EliteManager $eventManager)
    {
        $this->eliteManager = $eventManager;
    }
}

?>
