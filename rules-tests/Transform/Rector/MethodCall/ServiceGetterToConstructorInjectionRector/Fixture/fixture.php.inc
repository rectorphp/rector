<?php

namespace Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;

final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    public function __construct(FirstService $firstService)
    {
        $this->firstService = $firstService;
    }

    public function run()
    {
        $anotherService = $this->firstService->getAnotherService();
        $anotherService->run();
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;

final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    public function __construct(FirstService $firstService, private \Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService $anotherService)
    {
        $this->firstService = $firstService;
    }

    public function run()
    {
        $anotherService = $this->anotherService;
        $anotherService->run();
    }
}

?>
