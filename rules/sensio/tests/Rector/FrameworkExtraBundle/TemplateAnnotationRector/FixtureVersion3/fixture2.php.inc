<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService23Controller extends AbstractController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        return $this->render('AppBundle:ClassWithNamedService23:index.html.twig');
    }

    /**
     * @Template()
     */
    public function index2Action()
    {
        return $this->render('AppBundle:ClassWithNamedService23:index.html.twig', array(
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

class ClassWithNamedService23Controller extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('AppBundle:ClassWithNamedService23:index.html.twig');
    }

    public function index2Action(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('AppBundle:ClassWithNamedService23:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

?>
