<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService1Controller extends SymfonyController
{
    public function indexAction()
    {
        if(true){
            return $this->redirectToRoute('rector_is_cool');
        }

        return $this->render('AppBundle:ClassWithNamedService1:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
