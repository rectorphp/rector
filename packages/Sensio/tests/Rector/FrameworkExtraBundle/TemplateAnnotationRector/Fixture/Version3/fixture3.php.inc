<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService33Controller
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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService33Controller
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
