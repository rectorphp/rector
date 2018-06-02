<?php declare(strict_types=1);

use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class MyCommand extends ContainerAwareParentClass
{
    /**
     * @var \stdClass
     */
    private $stdClass;
    public function __construct(\stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
    protected function execute()
    {
        $someService = $this->stdClass;
    }
}
