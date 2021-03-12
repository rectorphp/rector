<?php

namespace Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source\Anything;
use Rector\Tests\Symfony\Rector\Source\SymfonyController;

class ClassWithNamedService2 extends SymfonyController
{
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->get(\Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source\Something::class)->callMe(),
            'comments' => $this->get(Anything::class)->callMe(),
        ]);
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source\Anything;
use Rector\Tests\Symfony\Rector\Source\SymfonyController;

class ClassWithNamedService2 extends SymfonyController
{
    public function __construct(private \Rector\Tests\Symfony\Rector\Tests\MethodCall\GetToConstructorInjectionRector\Source\Something $something, private \Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source\Anything $anything)
    {
    }
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->something->callMe(),
            'comments' => $this->anything->callMe(),
        ]);
    }
}

?>
