<?php declare (strict_types=1);

class ClassWithNamedService1 extends Controller
{
    public function indexAction()
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
