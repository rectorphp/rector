<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;
final class AnotherClassFactory
{
    /**
     * @var TurnMeToService
     */
    private $turnMeToService;
    public function __construct(TurnMeToService $turnMeToService)
    {
        $this->turnMeToService = $turnMeToService;
    }
    public function create(): \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClass
    {
        return new \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClass($this->turnMeToService);
    }
}
