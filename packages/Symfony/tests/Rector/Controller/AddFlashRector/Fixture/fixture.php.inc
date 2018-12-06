<?php

namespace Rector\Symfony\Tests\Rector\Controller\AddFlashRector\Wrong;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Symfony\Component\HttpFoundation\Request;

class AppController extends SymfonyController
{
    public function someAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', 'message');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Controller\AddFlashRector\Wrong;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Symfony\Component\HttpFoundation\Request;

class AppController extends SymfonyController
{
    public function someAction(Request $request)
    {
        $this->addFlash('success', 'message');
    }
}

?>
