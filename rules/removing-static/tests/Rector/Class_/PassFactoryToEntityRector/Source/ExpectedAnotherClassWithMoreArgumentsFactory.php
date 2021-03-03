<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;
final class AnotherClassWithMoreArgumentsFactory
{
    /**
     * @var TurnMeToService
     */
    private $turnMeToService;
    public function __construct(TurnMeToService $turnMeToService)
    {
        $this->turnMeToService = $turnMeToService;
    }
    public function create($number): \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClassWithMoreArguments
    {
        return new \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClassWithMoreArguments($number, $this->turnMeToService);
    }
}
