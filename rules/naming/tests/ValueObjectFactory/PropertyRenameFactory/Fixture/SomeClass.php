<?php

namespace Rector\Naming\Tests\ValueObjectFactory\PropertyRenameFactory\Fixture;

use Rector\Naming\Tests\ValueObjectFactory\PropertyRenameFactory\Source\EliteManager;

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
