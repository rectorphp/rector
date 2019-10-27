<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService45Controller
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

class ClassWithNamedService45Controller
{
    public function indexAction()
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}

?>
