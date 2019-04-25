<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;

class SomeClassWithMoreArguments
{
    public function run()
    {
        return new AnotherClassWithMoreArguments(10);
    }
}

class AnotherClassWithMoreArguments
{
    private $number;

    public function __construct($number)
    {
        $this->number = $number;
    }

    public function someFun()
    {
        return TurnMeToService::someStaticCall(5, $this->number);
    }
}

?>
-----
<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;

class SomeClassWithMoreArguments
{
    /**
     * @var \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClassWithMoreArgumentsFactory
     */
    private $anotherClassWithMoreArgumentsFactory;
    public function __construct(\Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClassWithMoreArgumentsFactory $anotherClassWithMoreArgumentsFactory)
    {
        $this->anotherClassWithMoreArgumentsFactory = $anotherClassWithMoreArgumentsFactory;
    }
    public function run()
    {
        return $this->anotherClassWithMoreArgumentsFactory->create(10);
    }
}

class AnotherClassWithMoreArguments
{
    private $number;
    /**
     * @var \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService
     */
    private $turnMeToService;

    public function __construct($number, \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService $turnMeToService)
    {
        $this->number = $number;
        $this->turnMeToService = $turnMeToService;
    }

    public function someFun()
    {
        return $this->turnMeToService->someStaticCall(5, $this->number);
    }
}

?>
