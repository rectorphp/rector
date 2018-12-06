<?php declare (strict_types=1);

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector\Wrong;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    public function someAction()
    {
        $this->getRequest()->getSomething();
    }
}
