<?php

namespace App\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService45Controller extends SymfonyController
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

    /**
     * @Template()
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return [];
    }
}

?>
-----
<?php

namespace App\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService45Controller extends SymfonyController
{
    public function indexAction()
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('class_with_named_service45/index.html.twig');
    }
}

?>
