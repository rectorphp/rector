<?php declare (strict_types=1);

use App\Service\Anything;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
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
