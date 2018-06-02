<?php declare (strict_types=1);

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService1 extends SymfonyController
{
    public function indexAction()
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
