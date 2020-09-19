<?php

namespace Rector\Naming\Tests\ValueObjectFactory\PropertyRenameFactory\FixtureBasic;

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

    public function getEliteManager(): EliteManager
    {
        return $this->eventManager;
    }
}

?>
