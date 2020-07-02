<?php

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\AbstractSymfonyController;

class SomeController extends AbstractSymfonyController
{
    public function action()
    {
        $request = $this->get('request');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\AbstractSymfonyController;

class SomeController extends AbstractSymfonyController
{
    public function action(\Symfony\Component\HttpFoundation\Request $request)
    {
        $request = $request;
    }
}

?>
