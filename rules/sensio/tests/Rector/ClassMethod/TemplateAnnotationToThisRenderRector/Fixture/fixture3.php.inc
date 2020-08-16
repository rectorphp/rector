<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService35Controller extends AbstractController
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService35Controller extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        if(true){
            return $this->redirectToRoute('rector_is_cool');
        }

        return $this->render('@App/Class_With_Named_Service35/index.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function index2Action(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('AppBundle:NameNotFollowingConvention:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

?>
