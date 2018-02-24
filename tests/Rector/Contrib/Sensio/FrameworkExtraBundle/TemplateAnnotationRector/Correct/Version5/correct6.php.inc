<?php declare (strict_types=1);

namespace AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     */
    public function indexAction()
    {
        if(true){
            return $this->redirectToRoute('rector_is_cool');
        }

        return $this->render('@App/class_with_named_service1/index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}