<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector\Fixture;

class HomepageController extends \AppController
{
    public function index()
    {
        $value = 5;
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector\Fixture;

class HomepageController extends \AppController
{
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $value = 5;
    }
}

?>
