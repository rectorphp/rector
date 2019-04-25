<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;

class SomeClass
{
    public function run()
    {
        return new AnotherClass;
    }
}

class AnotherClass
{
    public function someFun()
    {
        return TurnMeToService::someStaticCall();
    }
}

?>
-----
<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;

class SomeClass
{
    /**
     * @var \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClassFactory
     */
    private $anotherClassFactory;
    public function __construct(\Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Fixture\AnotherClassFactory $anotherClassFactory)
    {
        $this->anotherClassFactory = $anotherClassFactory;
    }
    public function run()
    {
        return $this->anotherClassFactory->create();
    }
}

class AnotherClass
{
    /**
     * @var \Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService
     */
    private $turnMeToService;
    public function __construct(\Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService $turnMeToService)
    {
        $this->turnMeToService = $turnMeToService;
    }
    public function someFun()
    {
        return $this->turnMeToService->someStaticCall();
    }
}

?>
