<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    public function render()
    {
        $someService = $this->get('some_service');

        $this->renderTwig([
            'posts' => $this->get('some_service')->callMe()
        ]);
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    private \stdClass $stdClass;
    public function __construct(\stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
    public function render()
    {
        $someService = $this->stdClass;

        $this->renderTwig([
            'posts' => $this->stdClass->callMe()
        ]);
    }
}

?>
