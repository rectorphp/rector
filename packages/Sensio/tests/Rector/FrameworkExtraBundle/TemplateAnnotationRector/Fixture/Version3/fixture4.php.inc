<?php declare (strict_types=1);

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService43 extends SymfonyController
{
    /**
     * @Template("AdminBundle:Payment:create.html.twig")
     */
    public function indexAction()
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}

?>
-----
<?php declare (strict_types=1);

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService43 extends SymfonyController
{
    public function indexAction()
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}

?>
