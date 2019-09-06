<?php

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\Anything;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService2 extends SymfonyController
{
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->get(\Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\Something::class)->callMe(),
            'comments' => $this->get(Anything::class)->callMe(),
        ]);
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Fixture;

use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\Anything;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService2 extends SymfonyController
{
    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\Something
     */
    private $something;
    /**
     * @var \Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\Anything
     */
    private $anything;
    public function __construct(\Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\Something $something, \Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\Anything $anything)
    {
        $this->something = $something;
        $this->anything = $anything;
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
