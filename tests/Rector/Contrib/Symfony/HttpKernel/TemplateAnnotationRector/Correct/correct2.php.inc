<?php declare (strict_types=1);

class ClassWithNamedService1 extends Controller
{
    /**
     */
    public function indexAction()
    {
        return $this->render('index.html.twig', ['someKey' => 'someValue']);
    }
}
