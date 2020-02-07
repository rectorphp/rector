<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\Fixture;

class SetController extends \AppController
{
    public function index()
    {
        $this->set('name', 5);
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\Fixture;

class SetController extends \AppController
{
    public function index()
    {
        return $this->render('homepage/index.twig', ['name' => 5]);
    }
}

?>
