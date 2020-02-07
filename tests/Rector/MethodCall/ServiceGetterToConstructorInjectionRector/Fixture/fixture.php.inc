<?php

namespace Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;

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

namespace Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;

final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;
    /**
     * @var \Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService
     */
    private $anotherService;

    public function __construct(FirstService $firstService, \Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService $anotherService)
    {
        $this->firstService = $firstService;
        $this->anotherService = $anotherService;
    }

    public function run()
    {
        $anotherService = $this->anotherService;
        $anotherService->run();
    }
}

?>
