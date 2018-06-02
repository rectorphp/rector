<?php declare(strict_types=1);

use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;

final class MyCommand extends ContainerAwareParentClass
{
    protected function execute()
    {
        $someService = $this->getContainer()->get('some_service');
    }
}
