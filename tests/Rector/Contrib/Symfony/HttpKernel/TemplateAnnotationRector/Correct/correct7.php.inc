<?php declare (strict_types=1);

namespace AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     */
    public function indexAction()
    {
        return $this->render('@App/NameNotFollowingConvention/index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
