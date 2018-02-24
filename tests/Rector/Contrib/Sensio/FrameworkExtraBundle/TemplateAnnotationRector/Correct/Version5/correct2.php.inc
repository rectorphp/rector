<?php declare (strict_types=1);

namespace Rector\AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     */
    public function indexAction()
    {
        return $this->render('@App/class_with_named_service1/index.html.twig', ['someKey' => 'someValue']);
    }
}
