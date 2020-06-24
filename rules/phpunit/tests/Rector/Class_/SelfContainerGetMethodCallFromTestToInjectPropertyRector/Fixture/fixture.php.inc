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
     * @inject
     */
    private \Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Fixture\SomeService $someService;
    public function test()
    {
        $this->someService->someMethod();
    }
}

class SomeService { }

?>
