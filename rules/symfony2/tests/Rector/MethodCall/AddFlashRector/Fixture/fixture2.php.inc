<?php

namespace Rector\Symfony2\Tests\Rector\MethodCall\AddFlashRector\Fixture;

use Symfony\Component\HttpFoundation\Request;

class Fixture2
{
    public function someAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', 'message');
    }
}
