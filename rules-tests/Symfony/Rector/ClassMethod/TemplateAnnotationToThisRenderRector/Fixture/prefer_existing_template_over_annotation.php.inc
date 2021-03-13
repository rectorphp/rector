<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService45Controller extends AbstractController
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
<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService45Controller extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}

?>
