<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService33Controller extends SymfonyController
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

    /**
     * @Template()
     */
    public function index2Action()
    {
        return $this->render('AppBundle:NameNotFollowingConvention:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

?>
-----
<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService33Controller extends SymfonyController
{
    public function indexAction()
    {
        if(true){
            return $this->redirectToRoute('rector_is_cool');
        }

        return $this->render('AppBundle:ClassWithNamedService33:index.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function index2Action()
    {
        return $this->render('AppBundle:NameNotFollowingConvention:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

?>
