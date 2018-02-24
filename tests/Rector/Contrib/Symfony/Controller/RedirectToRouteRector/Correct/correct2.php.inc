<?php declare (strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    public function someAction()
    {
        return $this->redirectToRoute('something', ['id' => $id]);
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}