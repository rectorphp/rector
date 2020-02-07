<?php

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Symfony\TestCase\Request;

class ClassWithParameterPresent extends SymfonyController
{
    public function someAction(Request $request)
    {
        $this->getRequest()->getSomething();
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Symfony\TestCase\Request;

class ClassWithParameterPresent extends SymfonyController
{
    public function someAction(Request $request, \Symfony\Component\HttpFoundation\Request $mainRequest)
    {
        $mainRequest->getSomething();
    }
}

?>
