<?php declare (strict_types=1);

namespace AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        return $this->render('@App/class_with_named_service1/index.html.twig');
    }
}
