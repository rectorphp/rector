<?php declare (strict_types=1);

namespace AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        if(true){
            return $this->redirectToRoute('rector_is_cool');
        }

        return array(
            'form' => $form->createView()
        );
    }
}
