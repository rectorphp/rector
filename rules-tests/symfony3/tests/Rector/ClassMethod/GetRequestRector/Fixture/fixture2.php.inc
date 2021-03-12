<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Symfony3\Tests\Rector\Source\SymfonyController;
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

namespace Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Symfony3\Tests\Rector\Source\SymfonyController;
use Symfony\TestCase\Request;

class ClassWithParameterPresent extends SymfonyController
{
    public function someAction(Request $request, \Symfony\Component\HttpFoundation\Request $mainRequest)
    {
        $mainRequest->getSomething();
    }
}

?>
