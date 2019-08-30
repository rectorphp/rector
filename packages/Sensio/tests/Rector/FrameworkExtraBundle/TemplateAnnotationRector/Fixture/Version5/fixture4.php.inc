<?php

namespace App\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
}

?>
-----
<?php

namespace App\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService45Controller extends SymfonyController
{
    public function indexAction()
    {
        return $this->render('payment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}

?>
