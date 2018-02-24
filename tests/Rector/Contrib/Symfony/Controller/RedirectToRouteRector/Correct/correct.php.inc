<?php declare (strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    public function someAction()
    {
        return $this->redirectToRoute('something');
    }
}