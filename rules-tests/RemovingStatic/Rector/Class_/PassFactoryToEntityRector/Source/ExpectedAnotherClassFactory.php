<?php

namespace Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Fixture;

final class AnotherClassFactory
{
    /**
     * @var \Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService
     */
    private $turnMeToService;
    public function __construct(\Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService $turnMeToService)
    {
        $this->turnMeToService = $turnMeToService;
    }
    public function create(): \Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClass
    {
        return new \Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClass($this->turnMeToService);
    }
}
