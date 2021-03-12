<?php

namespace Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;

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

namespace Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;

class SomeClassWithMoreArguments
{
    public function __construct(private \Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClassWithMoreArgumentsFactory $anotherClassWithMoreArgumentsFactory)
    {
    }
    public function run()
    {
        return $this->anotherClassWithMoreArgumentsFactory->create(10);
    }
}

class AnotherClassWithMoreArguments
{
    private $number;

    public function __construct($number, private \Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService $turnMeToService)
    {
        $this->number = $number;
    }

    public function someFun()
    {
        return $this->turnMeToService->someStaticCall(5, $this->number);
    }
}

?>
