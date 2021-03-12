<?php

namespace Rector\Tests\Symfony2\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController3 extends Controller
{
    public function someAction()
    {
        return $this->redirect($this->generateUrl('something', ['id' => $id]), 301);
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony2\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController3 extends Controller
{
    public function someAction()
    {
        return $this->redirectToRoute('something', ['id' => $id], 301);
    }
}

?>
