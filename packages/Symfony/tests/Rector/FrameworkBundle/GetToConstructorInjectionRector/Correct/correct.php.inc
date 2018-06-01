<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\FrameworkBundle\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    /**
     * @var \stdClass
     */
    private $stdClass;
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
