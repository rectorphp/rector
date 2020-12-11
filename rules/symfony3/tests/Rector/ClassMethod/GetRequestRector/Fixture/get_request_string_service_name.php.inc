<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Symfony3\Tests\Rector\Source\AbstractSymfonyController;

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

namespace Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Symfony3\Tests\Rector\Source\AbstractSymfonyController;

class SomeController extends AbstractSymfonyController
{
    public function action(\Symfony\Component\HttpFoundation\Request $request)
    {
        $request = $request;
    }
}

?>
