<?php declare (strict_types=1);

namespace AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     */
    public function indexAction()
    {
        return $this->render('AppBundle:ClassWithNamedService1:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}