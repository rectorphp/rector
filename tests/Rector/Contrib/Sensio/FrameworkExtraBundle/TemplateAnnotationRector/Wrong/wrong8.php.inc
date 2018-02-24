<?php declare (strict_types=1);

class ClassWithNamedService1 extends Controller
{
    /**
     * @Template("AdminBundle:Payment:create.html.twig")
     */
    public function indexAction()
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}