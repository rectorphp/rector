<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector\Fixture;

class ChangedViewPropertyController extends \AppController
{
    public function index()
    {
        $this->view = 'Controller/this_value';
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector\Fixture;

class ChangedViewPropertyController extends \AppController
{
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('Controller/this_value.twig');
    }
}

?>
