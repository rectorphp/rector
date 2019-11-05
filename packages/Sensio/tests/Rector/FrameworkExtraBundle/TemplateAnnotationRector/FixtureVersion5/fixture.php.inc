<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService15Controller extends AbstractController
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Template
     */
    public function index2Action()
    {
        return ['someKey' => 'someValue'];
    }

    /**
     * @Template("someFile.toBe.used")
     */
    public function index3Action()
    {
        return [];
    }
}

?>
-----
<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService15Controller extends AbstractController
{
    public function indexAction()
    {
        return $this->render('@App/class_with_named_service15/index.html.twig');
    }

    public function index2Action()
    {
        return $this->render('@App/class_with_named_service15/index2.html.twig', ['someKey' => 'someValue']);
    }

    public function index3Action()
    {
        return $this->render('someFile.toBe.used');
    }
}

?>
