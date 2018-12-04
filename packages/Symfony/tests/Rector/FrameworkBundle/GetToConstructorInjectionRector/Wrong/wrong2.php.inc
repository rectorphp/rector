<?php

use App\Service\Anything;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService2 extends SymfonyController
{
    public function render()
    {
        $this->renderTwig([
            'posts' => $this->get(\App\Service\Something::class)->callMe(),
            'comments' => $this->get(Anything::class)->callMe(),
        ]);
    }
}

?>
-----
<?php

use App\Service\Anything;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService2 extends SymfonyController
{
    /**
     * @var \App\Service\Something
     */
    private $something;
    /**
     * @var \App\Service\Anything
     */
    private $anything;
    public function __construct(\App\Service\Something $something, \App\Service\Anything $anything)
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
