<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\Fixture;

class RedirectController extends \AppController
{
    public function index()
    {
        $this->render('custom_file');
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\Fixture;

class RedirectController extends \AppController
{
    public function index()
    {
        return $this->render('redirect/custom_file.twig');
    }
}

?>
