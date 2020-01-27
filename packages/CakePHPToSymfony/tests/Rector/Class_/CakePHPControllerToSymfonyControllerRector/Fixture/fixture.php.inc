<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerToSymfonyControllerRector\Fixture;

class HomepageController extends \AppController
{
    public function index()
    {
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerToSymfonyControllerRector\Fixture;

class HomepageController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function index()
    {
    }
}

?>
