<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\Fixture;

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

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\Fixture;

class ChangedViewPropertyController extends \AppController
{
    public function index()
    {
        return $this->render('controller/this_value.twig');
    }
}

?>
