<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService13Controller
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

class ClassWithNamedService13Controller
{
    public function indexAction()
    {
        return $this->render('AppBundle:ClassWithNamedService13:index.html.twig');
    }

    public function index2Action()
    {
        return $this->render('AppBundle:ClassWithNamedService13:index2.html.twig', ['someKey' => 'someValue']);
    }

    public function index3Action()
    {
        return $this->render('someFile.toBe.used');
    }
}

?>
