<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\ValueObjectFactory\PropertyRenameFactory\Fixture;

use Rector\Naming\Tests\ValueObjectFactory\PropertyRenameFactory\Source\EliteManager;

class SkipSomeClass
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
