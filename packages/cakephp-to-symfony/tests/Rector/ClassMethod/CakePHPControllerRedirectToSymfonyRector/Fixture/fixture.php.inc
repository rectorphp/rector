<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRedirectToSymfonyRector\Fixture;

class RedirectController extends \AppController
{
    public function index()
    {
        $this->redirect('boom');
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRedirectToSymfonyRector\Fixture;

class RedirectController extends \AppController
{
    public function index()
    {
        return $this->redirect('boom');
    }
}

?>
