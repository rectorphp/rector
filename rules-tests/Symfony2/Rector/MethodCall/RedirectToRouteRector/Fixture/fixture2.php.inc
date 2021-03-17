<?php

namespace Rector\Tests\Symfony2\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController2 extends Controller
{
    public function someAction()
    {
        return $this->redirect($this->generateUrl('something', ['id' => $id]));
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony2\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController2 extends Controller
{
    public function someAction()
    {
        return $this->redirectToRoute('something', ['id' => $id]);
    }
}

?>
