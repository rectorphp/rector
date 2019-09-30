<?php

namespace Rector\Symfony\Tests\Rector\Controller\AddFlashRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AppController extends Controller
{
    public function someAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', 'message');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Controller\AddFlashRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AppController extends Controller
{
    public function someAction(Request $request)
    {
        $this->addFlash('success', 'message');
    }
}

?>
