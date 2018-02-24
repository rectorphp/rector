<?php declare (strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AppController extends Controller
{
    public function someAction(Request $request)
    {
        $this->addFlash('success', 'message');
    }
}
