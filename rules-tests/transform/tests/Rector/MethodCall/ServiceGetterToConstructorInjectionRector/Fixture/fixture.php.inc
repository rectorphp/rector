<?php

namespace Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Fixture;

use Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;

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

namespace Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Fixture;

use Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;

final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    public function __construct(FirstService $firstService, private \Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService $anotherService)
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
