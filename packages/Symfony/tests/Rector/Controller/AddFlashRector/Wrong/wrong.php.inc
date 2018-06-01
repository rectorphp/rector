<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Symfony\Component\HttpFoundation\Request;

class AppController extends SymfonyController
{
    public function someAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', 'message');
    }
}
