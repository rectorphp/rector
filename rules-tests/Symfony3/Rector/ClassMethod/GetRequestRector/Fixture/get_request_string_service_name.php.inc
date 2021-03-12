<?php

namespace Rector\Tests\Symfony3\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Tests\Symfony3\Rector\Source\AbstractSymfonyController;

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

namespace Rector\Tests\Symfony3\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Tests\Symfony3\Rector\Source\AbstractSymfonyController;

class SomeController extends AbstractSymfonyController
{
    public function action(\Symfony\Component\HttpFoundation\Request $request)
    {
        $request = $request;
    }
}

?>
