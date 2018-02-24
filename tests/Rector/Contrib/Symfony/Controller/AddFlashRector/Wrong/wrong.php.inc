<?php declare (strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AppController extends Controller
{
    public function someAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', 'message');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}