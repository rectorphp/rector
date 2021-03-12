<?php

namespace Rector\Tests\Symfony3\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Tests\Symfony3\Rector\Source\SymfonyController;
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

namespace Rector\Tests\Symfony3\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Tests\Symfony3\Rector\Source\SymfonyController;
use Symfony\TestCase\Request;

class ClassWithParameterPresent extends SymfonyController
{
    public function someAction(Request $request, \Symfony\Component\HttpFoundation\Request $mainRequest)
    {
        $mainRequest->getSomething();
    }
}

?>
