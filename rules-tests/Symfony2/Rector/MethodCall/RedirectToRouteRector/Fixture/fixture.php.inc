<?php

namespace Rector\Tests\Symfony2\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    public function someAction()
    {
        return $this->redirect($this->generateUrl('something'));
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony2\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    public function someAction()
    {
        return $this->redirectToRoute('something');
    }
}

?>
