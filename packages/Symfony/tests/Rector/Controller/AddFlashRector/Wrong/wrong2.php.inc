<?php

namespace Rector\Symfony\Tests\Rector\Controller\AddFlashRector\Wrong;

use Symfony\Component\HttpFoundation\Request;

class AnyClass2
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

use Symfony\Component\HttpFoundation\Request;

class AnyClass2
{
    public function someAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', 'message');
    }
}

?>
