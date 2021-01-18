<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\Anything;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService2 extends SymfonyController
{
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->get(\Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\Something::class)->callMe(),
            'comments' => $this->get(Anything::class)->callMe(),
        ]);
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\Anything;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService2 extends SymfonyController
{
    public function __construct(private \Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\Something $something, private \Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\Anything $anything)
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
