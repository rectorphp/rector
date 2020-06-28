<?php

namespace Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Fixture;

use Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Source\EventManager;
final class ExtraUsingProperty extends \Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Source\AbstractFather
{
    /**
     * @var EventManager
     */
    private $eventManager;
    public function __construct($eventManager)
    {
        $this->eventManager = $eventManager;
    }
    public function newUsingProperty()
    {
        return $this->eventManager->runEvent();
    }
}
