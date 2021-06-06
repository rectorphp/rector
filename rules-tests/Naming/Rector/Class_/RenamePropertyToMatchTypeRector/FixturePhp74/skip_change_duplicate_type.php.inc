<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp74;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\EliteManager;

class SomeClass
{
    /**
     * @var EliteManager
     */
    private $eventManager1;

    /**
     * @var EliteManager
     */
    private $eventManager2;

    public function __construct(EliteManager $eventManager1, EliteManager $eventManager2)
    {
        $this->eventManager1 = $eventManager1;
        $this->eventManager2 = $eventManager2;
    }
}
