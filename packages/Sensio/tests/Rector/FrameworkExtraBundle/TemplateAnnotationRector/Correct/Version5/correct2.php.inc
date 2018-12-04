<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService25Controller extends SymfonyController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        return $this->render('AppBundle:ClassWithNamedService25:index.html.twig');
    }

    /**
     * @Template()
     */
    public function index2Action()
    {
        return $this->render('AppBundle:ClassWithNamedService25:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

?>
-----
<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService25Controller extends SymfonyController
{
    public function indexAction()
    {
        return $this->render('AppBundle:ClassWithNamedService25:index.html.twig');
    }

    public function index2Action()
    {
        return $this->render('AppBundle:ClassWithNamedService25:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

?>
