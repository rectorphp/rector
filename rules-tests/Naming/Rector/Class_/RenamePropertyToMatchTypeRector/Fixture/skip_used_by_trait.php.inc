<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\EliteManager;
use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\UseEliteManagerTrait;

class SkipUsedByTrait
{
    use UseEliteManagerTrait;

    /**
     * @var EliteManager
     */
    private $eventManager;

    public function __construct(EliteManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
