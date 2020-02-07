<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Fixture;

use Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Source\TestCaseWithGetContainer;

class SomeClassTest extends TestCaseWithGetContainer
{
    public function test()
    {
        $someService = $this->getContainer()->get(SomeService::class);
        $someService->someMethod();
    }
}

class SomeService { }

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Fixture;

use Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Source\TestCaseWithGetContainer;

class SomeClassTest extends TestCaseWithGetContainer
{
    /**
     * @var \Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Fixture\SomeService
     * @inject
     */
    private $someService;
    public function test()
    {
        $this->someService->someMethod();
    }
}

class SomeService { }

?>
