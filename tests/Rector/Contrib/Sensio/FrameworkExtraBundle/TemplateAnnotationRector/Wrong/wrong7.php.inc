<?php declare (strict_types=1);

namespace AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        return $this->render('AppBundle:NameNotFollowingConvention:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}