<?php declare (strict_types=1);

use Symfony\Component\HttpFoundation\Request;

class AnyClass
{
    public function someAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', 'message');
    }
}